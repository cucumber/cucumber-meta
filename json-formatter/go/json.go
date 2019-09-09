package json

import (
	"encoding/json"
	"fmt"
	"io"
	"strings"

	messages "github.com/cucumber/cucumber-messages-go/v5"
	gio "github.com/gogo/protobuf/io"
)

type jsonFeature struct {
	Description string               `json:"description"`
	Elements    []jsonFeatureElement `json:"elements"`
	ID          string               `json:"id"`
	Keyword     string               `json:"keyword"`
	Line        uint32               `json:"line"`
	Name        string               `json:"name"`
	URI         string               `json:"uri"`
}

type jsonFeatureElement struct {
	Description string      `json:"description"`
	ID          string      `json:"id,omitempty"`
	Keyword     string      `json:"keyword"`
	Line        uint32      `json:"line"`
	Name        string      `json:"name"`
	Steps       []*jsonStep `json:"steps"`
	Type        string      `json:"type"`
}

type jsonStep struct {
	Keyword string          `json:"keyword"`
	Line    uint32          `json:"line"`
	Name    string          `json:"name"`
	Result  *jsonStepResult `json:"result"`
}

type jsonStepResult struct {
	Duration     uint64 `json:"duration"`
	Status       string `json:"status"`
	ErrorMessage string `json:"error_message,omitempty"`
}

type Formatter struct {
	jsonFeatures         []*jsonFeature
	jsonFeaturesByURI    map[string]*jsonFeature
	jsonStepsByKey       map[string]*jsonStep
	gherkinDocumentByURI map[string]*messages.GherkinDocument
	pickleById           map[string]*messages.Pickle
	backgroundByUri      map[string]*messages.GherkinDocument_Feature_Background
	scenariosByKey       map[string]*messages.GherkinDocument_Feature_Scenario
	backgroundStepsByKey map[string]*messages.GherkinDocument_Feature_Step
	scenarioStepsByKey   map[string]*messages.GherkinDocument_Feature_Step
}

// ProcessMessages writes a JSON report to STDOUT
func (formatter *Formatter) ProcessMessages(stdin io.Reader, stdout io.Writer) (err error) {
	formatter.jsonFeatures = make([]*jsonFeature, 0)
	formatter.jsonFeaturesByURI = make(map[string]*jsonFeature)
	formatter.jsonStepsByKey = make(map[string]*jsonStep)

	formatter.gherkinDocumentByURI = make(map[string]*messages.GherkinDocument)
	formatter.pickleById = make(map[string]*messages.Pickle)
	formatter.backgroundByUri = make(map[string]*messages.GherkinDocument_Feature_Background)
	formatter.scenariosByKey = make(map[string]*messages.GherkinDocument_Feature_Scenario)
	formatter.backgroundStepsByKey = make(map[string]*messages.GherkinDocument_Feature_Step)
	formatter.scenarioStepsByKey = make(map[string]*messages.GherkinDocument_Feature_Step)

	r := gio.NewDelimitedReader(stdin, 4096)

	for {
		wrapper := &messages.Envelope{}
		err := r.ReadMsg(wrapper)
		if err == io.EOF {
			break
		}
		if err != nil {
			return err
		}

		switch m := wrapper.Message.(type) {
		case *messages.Envelope_GherkinDocument:
			formatter.gherkinDocumentByURI[m.GherkinDocument.Uri] = m.GherkinDocument
			for _, child := range m.GherkinDocument.Feature.Children {
				if child.GetBackground() != nil {
					formatter.backgroundByUri[m.GherkinDocument.Uri] = child.GetBackground()
					for _, step := range child.GetBackground().Steps {
						formatter.backgroundStepsByKey[key(m.GherkinDocument.Uri, step.Location)] = step
					}
				}

				if child.GetScenario() != nil {
					formatter.scenariosByKey[key(m.GherkinDocument.Uri, child.GetScenario().Location)] = child.GetScenario()
					for _, step := range child.GetScenario().Steps {
						formatter.scenarioStepsByKey[key(m.GherkinDocument.Uri, step.Location)] = step
					}
				}
			}

		case *messages.Envelope_Pickle:
			pickle := m.Pickle
			formatter.pickleById[pickle.Id] = pickle
			jsonFeature := formatter.findOrCreateJsonFeature(pickle)

			scenario := formatter.scenariosByKey[key(pickle.Uri, pickle.Locations[0])]

			scenarioJsonSteps := make([]*jsonStep, 0)
			backgroundJsonSteps := make([]*jsonStep, 0)

			for _, pickleStep := range pickle.Steps {
				isBackgroundStep := false
				step := formatter.scenarioStepsByKey[key(pickle.Uri, pickleStep.Locations[0])]

				if step == nil {
					step = formatter.backgroundStepsByKey[key(pickle.Uri, pickleStep.Locations[0])]
					isBackgroundStep = true
				}

				jsonStep := &jsonStep{
					Keyword: step.Keyword,
					Line:    step.Location.Line,
					Name:    step.Text,
				}
				if isBackgroundStep {
					backgroundJsonSteps = append(backgroundJsonSteps, jsonStep)
				} else {
					scenarioJsonSteps = append(scenarioJsonSteps, jsonStep)
				}

				formatter.jsonStepsByKey[key(pickle.Uri, step.Location)] = jsonStep
			}

			if len(backgroundJsonSteps) > 0 {
				background := formatter.backgroundByUri[pickle.Uri]
				jsonFeature.Elements = append(jsonFeature.Elements, jsonFeatureElement{
					Description: background.Description,
					Keyword:     background.Keyword,
					Line:        background.Location.Line,
					Steps:       backgroundJsonSteps,
					Type:        "background",
				})
			}

			jsonFeature.Elements = append(jsonFeature.Elements, jsonFeatureElement{
				Description: scenario.Description,
				ID:          fmt.Sprintf("%s;%s", jsonFeature.ID, idify(scenario.Name)),
				Keyword:     scenario.Keyword,
				Line:        scenario.Location.Line,
				Name:        scenario.Name,
				Steps:       scenarioJsonSteps,
				Type:        "scenario",
			})

		case *messages.Envelope_TestStepFinished:
			pickle := formatter.pickleById[m.TestStepFinished.PickleId]
			pickleStep := pickle.Steps[m.TestStepFinished.Index]

			step := formatter.jsonStepsByKey[key(pickle.Uri, pickleStep.Locations[0])]

			status := strings.ToLower(m.TestStepFinished.TestResult.Status.String())
			step.Result = &jsonStepResult{
				Duration:     m.TestStepFinished.TestResult.DurationNanoseconds,
				Status:       status,
				ErrorMessage: m.TestStepFinished.TestResult.Message,
			}
		}
	}

	output, _ := json.MarshalIndent(formatter.jsonFeatures, "", "  ")
	_, err = fmt.Fprintln(stdout, string(output))
	return err
}

func (formatter *Formatter) findOrCreateJsonFeature(pickle *messages.Pickle) *jsonFeature {
	jFeature, ok := formatter.jsonFeaturesByURI[pickle.Uri]
	if !ok {
		gherkinDocumentFeature := formatter.gherkinDocumentByURI[pickle.Uri].Feature

		jFeature = &jsonFeature{
			Description: gherkinDocumentFeature.Description,
			Elements:    make([]jsonFeatureElement, 0),
			ID:          idify(gherkinDocumentFeature.Name),
			Keyword:     gherkinDocumentFeature.Keyword,
			Line:        gherkinDocumentFeature.Location.Line,
			Name:        gherkinDocumentFeature.Name,
			URI:         pickle.Uri,
		}
		formatter.jsonFeaturesByURI[pickle.Uri] = jFeature
		formatter.jsonFeatures = append(formatter.jsonFeatures, jFeature)
	}
	return jFeature
}

func key(uri string, location *messages.Location) string {
	return fmt.Sprintf("%s:%d", uri, location.Line)
}

func idify(s string) string {
	return strings.ToLower(strings.Replace(s, " ", "-", -1))
}

package io.cucumber.gherkin;

import io.cucumber.gherkin.pickles.PickleCompiler;
import io.cucumber.messages.IdGenerator;
import org.junit.Test;

import java.util.List;

import static io.cucumber.messages.Messages.Comment;
import static io.cucumber.messages.Messages.FeatureChild;
import static io.cucumber.messages.Messages.GherkinDocument;
import static io.cucumber.messages.Messages.Pickle;
import static io.cucumber.messages.Messages.TableRow;
import static org.junit.Assert.assertEquals;

public class GherkinDocumentBuilderTest {
    private final IdGenerator idGenerator = new IdGenerator.Incrementing();

    @Test
    public void is_reusable() {
        Parser<GherkinDocument> parser = new Parser<>(new GherkinDocumentBuilder(idGenerator));
        TokenMatcher matcher = new TokenMatcher();

        GherkinDocument d1 = parser.parse("Feature: 1", matcher);
        GherkinDocument d2 = parser.parse("Feature: 2", matcher);

        assertEquals("1", d1.getFeature().get().getName());
        assertEquals("2", d2.getFeature().get().getName());
    }

    @Test
    public void parses_rules() {
        Parser<GherkinDocument> parser = new Parser<>(new GherkinDocumentBuilder(idGenerator));
        String data = "" +
                "Feature: Some rules\n" +
                "\n" +
                "  Background:\n" +
                "    Given fb\n" +
                "\n" +
                "  Rule: A\n" +
                "    The rule A description\n" +
                "\n" +
                "    Background:\n" +
                "      Given ab\n" +
                "\n" +
                "    Example: Example A\n" +
                "      Given a\n" +
                "\n" +
                "  Rule: B\n" +
                "    The rule B description\n" +
                "\n" +
                "    Example: Example B\n" +
                "      Given b";
        GherkinDocument doc = parser.parse(data);

        List<FeatureChild> children = doc.getFeature().get().getChildren();
        assertEquals(3, children.size());

        IdGenerator idGenerator = new IdGenerator.Incrementing();
        PickleCompiler pickleCompiler = new PickleCompiler(idGenerator);
        List<Pickle> pickles = pickleCompiler.compile(doc, "hello.feature");
        assertEquals(2, pickles.size());

        assertEquals(3, pickles.get(0).getSteps().size());

        assertEquals(2, pickles.get(1).getSteps().size());
    }

    @Test
    public void parses_just_comments() {
        Parser<GherkinDocument> parser = new Parser<>(new GherkinDocumentBuilder(idGenerator));
        GherkinDocument doc = parser.parse("" +
                "# Just a comment");
        List<Comment> children = doc.getComments();
        assertEquals(1, children.size());
    }

    @Test
    public void sets_empty_table_cells() {
        Parser<GherkinDocument> parser = new Parser<>(new GherkinDocumentBuilder(idGenerator));
        GherkinDocument doc = parser.parse("" +
                "Feature:\n" +
                "  Scenario:\n" +
                "    Given a table\n" +
                "      |a||b|"
        );
        TableRow row = doc.getFeature().get().getChildren().get(0).getScenario().get().getSteps().get(0).getDataTable().get().getRows().get(0);
        assertEquals("a", row.getCells().get(0).getValue());
        assertEquals("", row.getCells().get(1).getValue());
        assertEquals("b", row.getCells().get(2).getValue());
    }
}

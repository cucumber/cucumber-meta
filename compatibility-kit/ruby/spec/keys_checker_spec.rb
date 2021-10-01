require 'rspec'
require 'cucumber/messages'
require_relative '../lib/keys_checker'

describe CCK::KeysChecker do
  let(:subject) { CCK::KeysChecker }

  describe '#compare' do
    let(:complete) do
      Cucumber::Messages::PickleStepArgument.new(
        doc_string: '1',
        data_table: '12'
      )
    end

    let(:missing_data_table) do
      Cucumber::Messages::PickleStepArgument.new(
        doc_string: '1'
      )
    end

    let(:missing_doc_string) do
      Cucumber::Messages::PickleStepArgument.new(
        data_table: '12'
      )
    end

    let(:wrong_values) do
      Cucumber::Messages::PickleStepArgument.new(
        doc_string: '123',
        data_table: '456'
      )
    end

    it 'finds missing key' do
      expect(subject.compare(missing_data_table, complete)).to eq(
        ['Missing keys in message Cucumber::Messages::PickleStepArgument: [:data_table]']
      )
    end

    it 'finds extra keys' do
      expect(subject.compare(complete, missing_doc_string)).to eq(
        ['Found extra keys in message Cucumber::Messages::PickleStepArgument: [:doc_string]']
      )
    end

    it 'finds extra and missing' do
      expect(subject.compare(missing_doc_string, missing_data_table)).to contain_exactly(
        'Missing keys in message Cucumber::Messages::PickleStepArgument: [:doc_string]',
        'Found extra keys in message Cucumber::Messages::PickleStepArgument: [:data_table]'
      )
    end

    it 'does not care about the values' do
      expect(subject.compare(complete, wrong_values)).to be_empty
    end

    context 'when default values are omitted' do
      let(:default_set) do
        Cucumber::Messages::Duration.new(
          seconds: 0,
          nanos: 12
        )
      end

      let(:default_not_set) do
        Cucumber::Messages::Duration.new(
          nanos: 12
        )
      end

      it 'does not raise an exception' do
        expect(subject.compare(default_set, default_not_set)).to be_empty
      end
    end
  end
end

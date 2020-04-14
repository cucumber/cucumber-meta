package io.cucumber.cucumberexpressions;

import org.apiguardian.api.API;

import java.lang.reflect.Type;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

import static io.cucumber.cucumberexpressions.ParameterType.createAnonymousParameterType;

@API(status = API.Status.STABLE)
public final class RegularExpression implements Expression {
    private final Pattern expressionRegexp;
    private final ParameterTypeRegistry parameterTypeRegistry;
    private final TreeRegexp treeRegexp;

    /**
     * Creates a new instance. Use this when the transform types are not known in advance,
     * and should be determined by the regular expression's capture groups. Use this with
     * dynamically typed languages.
     *
     * @param expressionRegexp      the regular expression to use
     * @param parameterTypeRegistry used to look up parameter types
     */
    RegularExpression(Pattern expressionRegexp, ParameterTypeRegistry parameterTypeRegistry) {
        this.expressionRegexp = expressionRegexp;
        this.parameterTypeRegistry = parameterTypeRegistry;
        this.treeRegexp = new TreeRegexp(expressionRegexp);
    }

    @Override
    public List<Argument<?>> match(String text, Type... typeHints) {
        final ParameterByTypeTransformer defaultTransformer = parameterTypeRegistry.getDefaultParameterTransformer();
        final List<ParameterType<?>> parameterTypes = new ArrayList<>();
        int typeHintIndex = 0;
        for (GroupBuilder groupBuilder : treeRegexp.getGroupBuilder().getChildren()) {
            final String parameterTypeRegexp = groupBuilder.getSource();
            final boolean hasTypeHint = typeHintIndex < typeHints.length;
            final Type typeHint = hasTypeHint ? typeHints[typeHintIndex++] : String.class;

            ParameterType<?> parameterType = parameterTypeRegistry.lookupByRegexp(parameterTypeRegexp, expressionRegexp, text);

            // When there is a conflict between the type from the regular expression and the
            // type from the method, prefer the type from the method. If we contradict the
            // type from the method, we'll throw an exception when we invoke the glue with
            // an argument of the wrong type.
            if (parameterType != null && hasTypeHint && !parameterType.getType().equals(typeHint)) {
                parameterType = null;
            }

            if (parameterType == null) {
                parameterType = createAnonymousParameterType(parameterTypeRegexp);
            }

            // Either from createAnonymousParameterType or lookupByRegexp
            if (parameterType.isAnonymous()) {
                parameterType = parameterType.deAnonymize(typeHint, arg -> defaultTransformer.transform(arg, typeHint));
            }

            parameterTypes.add(parameterType);
        }


        return Argument.build(treeRegexp, text, parameterTypes);
    }

    @Override
    public Pattern getRegexp() {
        return expressionRegexp;
    }

    @Override
    public String getSource() {
        return expressionRegexp.pattern();
    }
}

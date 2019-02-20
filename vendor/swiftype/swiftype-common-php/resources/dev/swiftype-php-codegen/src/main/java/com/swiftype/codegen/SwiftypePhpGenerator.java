package com.swiftype.codegen;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Set;

import org.apache.commons.lang3.StringUtils;
import org.openapitools.codegen.CliOption;
import org.openapitools.codegen.CodegenConfig;
import org.openapitools.codegen.CodegenOperation;
import org.openapitools.codegen.CodegenType;
import org.openapitools.codegen.CodegenParameter;
import org.openapitools.codegen.SupportingFile;
import org.openapitools.codegen.languages.PhpClientCodegen;
import org.openapitools.codegen.utils.ModelUtils;

import io.swagger.v3.oas.models.Operation;
import io.swagger.v3.oas.models.media.Schema;
import io.swagger.v3.oas.models.parameters.Parameter;

public class SwiftypePhpGenerator extends PhpClientCodegen implements CodegenConfig {

  public static final String GENERATOR_NAME = "swiftype-php";
  public static final String HELP_URL       = "helpUrl";
  public static final String COPYRIGHT      = "copyright";

  public SwiftypePhpGenerator() {
    super();
    
    cliOptions.add(new CliOption(HELP_URL, "Help URL"));
    cliOptions.add(new CliOption(COPYRIGHT, "Copyright"));

    this.setTemplateDir(SwiftypePhpGenerator.GENERATOR_NAME);
    this.setSrcBasePath("");
    this.embeddedTemplateDir = this.templateDir();

    this.apiDirName = "Endpoint";
    setApiPackage(getInvokerPackage() + "\\" + apiDirName);
    this.setParameterNamingConvention("camelCase");
  }

  @Override
  public void processOpts() {
    super.processOpts();
    this.resetTemplateFiles();

    supportingFiles.add(new SupportingFile("Client.mustache", "", "Client.php"));
    supportingFiles.add(new SupportingFile("README.mustache", "", "README.md"));
  }

  @Override
  public CodegenType getTag() {
    return CodegenType.CLIENT;
  }

  @Override
  public String getName() {
    return SwiftypePhpGenerator.GENERATOR_NAME;
  }

  @Override
  public String toApiName(String name) {
    return initialCaps(name);
  }

  @Override
  @SuppressWarnings("static-method")
  public void addOperationToGroup(String tag, String resourcePath,
      Operation operation, CodegenOperation co,
      Map<String, List<CodegenOperation>> operations) {
    String uniqueName = co.operationId;
    List<CodegenOperation> opList = new ArrayList<CodegenOperation>();
    co.operationIdLowerCase = uniqueName.toLowerCase(Locale.ROOT);
    co.operationIdCamelCase = org.openapitools.codegen.utils.StringUtils.camelize(uniqueName);
    co.operationIdSnakeCase = org.openapitools.codegen.utils.StringUtils.underscore(uniqueName);

    opList.add(co);
    operations.put(uniqueName, opList);
  }

  @Override
  public String getTypeDeclaration(Schema p) {
    if (ModelUtils.isArraySchema(p) || ModelUtils.isMapSchema(p)) {
      return "array";
    } else if (ModelUtils.isObjectSchema(p) || ModelUtils.isModel(p) || StringUtils.isNotBlank(p.get$ref())) {
      return "array";
    }

    return super.getTypeDeclaration(p);
  }

  @Override
  public String getTypeDeclaration(String name) {
    if (!languageSpecificPrimitives.contains(name)) {
      return "array";
    }

    return super.getTypeDeclaration(name);
  }

  @Override
  public CodegenParameter fromParameter(Parameter parameter, Set<String> imports) {
    CodegenParameter codegenParameter = super.fromParameter(parameter, imports);

    if (parameter.getExtensions() != null && parameter.getExtensions().containsKey("x-codegen-param-name")) {
        codegenParameter.paramName = parameter.getExtensions().get("x-codegen-param-name").toString();
    }

    return codegenParameter;
  }

  private void resetTemplateFiles() {
    this.supportingFiles.clear();
    this.apiTemplateFiles.clear();
    this.apiTestTemplateFiles.clear();
    this.apiDocTemplateFiles.clear();
    this.modelTemplateFiles.clear();
    this.modelTestTemplateFiles.clear();
    this.modelDocTemplateFiles.clear();

    apiTemplateFiles.put("api.mustache", ".php");
  }
}
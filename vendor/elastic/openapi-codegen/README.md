# Elastic OpenAPI PHP client generator.

<p align="center"><a href="https://circleci.com/gh/elastic/openapi-codegen-php"><img src="https://circleci.com/gh/elastic/openapi-codegen-php.svg?style=svg" alt="CircleCI build"></a></p>

> Make easier clients creation by generating most of the code from an Open API specification.
>
> Projects using the Elastic OpenAPI PHP client generator:
>
> * [Elastic Site Search Official PHP client](https://github.com/elastic/site-search-php/)
> * [Elastic App Search Official PHP client](https://github.com/elastic/app-search-php/)


## Contents

- [Getting started](#getting-started-with-the-generator-)
- [Using the client](#Using-the-client)
- [FAQ](#faq-)
- [Contribute](#contribute-)
- [License](#license-)

***

## Getting started with the generator 🐣

### Requirements

We assume you have the following components installed and available in your environment :

> * Docker (used to run the code generator)
> * composer

### Initiliaze project

When you want to create a new client you have first to create a new composer project :

```bash
composer create-project my-new-fancy-client
```

Once the project is created you should pimp up your `composer.json` file (package name, author, ...).
Make sure the autoload section contains the PHP namespace you want to use for your client (here `Fancy\Client`):

```json
"autoload": {
  "psr-4": {
    "Fancy\\Client\\": ""
  }
}
```

Once the project is created , you have to append the code generator as a requirement of the project:

```bash
composer require elastic/openapi-codegen
```

### Configuring the generator

By convention, the code generator expect the `resources/api` folder containing two files :

* **`api-spec.yml`** : The OpenAPI specification that describe the server API. You can find a full featured example at : https://github.com/swiftype/swiftype-site-search-php/blob/master/resources/api/api-spec.yml

* **`config.json`** : A configuration file contains important variable variables that allow to configure both code and documentation generation :

```json
{
    "gitUserId": "myorg",
    "gitRepoId": "my-new-fancy-client",
    "artifactVersion": "1.0.0",
    "invokerPackage": "Fancy\\Client",
    "helpUrl": "https://discuss.elastic.co/c/site-search",
    "copyright": "© [Elastic](https://github.com/elastic)"
}
```

### Running the generator

Once the project is setup and the generator is configured, you can run code generation by using the launcher script from the root of your project :

```
vendor/bin/elastic-openapi-codegen.sh
```

The generator will create or update the following files in your project :

- **`Client.php`**: The client class that contains one method for each paths / operation of your specification.

- **`Endpoint/*.php`**: One endpoint class for each path / operation of your specification.

- **`README.md`**: The Readme of your project (see [here](#Customize-Documentation) for how to customize the documentation)

### Create the client builder

Client instantiation logic is very specific for each project and need to be customized for each project (authentication management, error handling). At the same time end users of your client expect a very easy to use method to instantiate the client.

Here is a code for providing a very basic client builder to end users:

```php
namespace Fancy\Client;

class ClientBuilder extends \Elastic\OpenApi\Codegen\AbstractClientBuilder
{
    /**
     * Return the configured client.
     *
     * @return \Fancy\Client\Client
     */
    public function build()
    {
        return new Client($this->getEndpointBuilder(), $this->getConnection());
    }

    /**
     * Endpoint builder is in charge of resolving the endpoint classes.
     * Need to be configured with your own namespace.
     */
    protected function getEndpointBuilder()
    {
        return new \Elastic\OpenApi\Codegen\Endpoint\Builder(__NAMESPACE__ . '\Endpoint');
    }
}
```

## Using the client

Once you will have fulfilled the tasks above, it is very simple for end user to get client they can use :

```php
$clientBuilder = new \Fancy\Client\ClientBuilder();
$client = $clientBuilder->build();
```

### Customization

Now you have a working client, there is several things that you may want to customize :

- Client and Endpoint code generation
- Documentation generation
- Connection logic : request and response handling, authentication, ...

You can find a full documentation of available extension point in the [Customization documentation](docs/Customization.md).

## FAQ 🔮

### Where do I report issues with the client?

If something is not working as expected, please open an [issue](https://github.com/elastic/openapi-codegen-php/issues/new).


## Contribute 🚀

We welcome contributors to the project. Before you begin, a couple notes...

+ Before opening a pull request, please create an issue to [discuss the scope of your proposal](https://github.com/elastic/openapi-codegen-php/issues).
+ Please write simple code and concise documentation, when appropriate.

## License 📗

[Apache 2.0](https://github.com/elastic/openapi-codegen-php/blob/master/LICENSE) © [Elastic](https://github.com/elastic)

Thank you to all the [contributors](https://github.com/elastic/openapi-codegen-php/graphs/contributors)!

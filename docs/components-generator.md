# Components Generator
Use the component generator utility to create scaffoling for a new component.

### Usage
```shell
$ php craft components/component/generate <"Component Display Name">
```
* "Component Display Name" - Required
  * Name for the component, must be wrapped in quotes if it is multiple words
  * Name will automatically be converted to PascalCase and camelCase where needed

Example Output:
```shell
╔══════════════════════════════╤═══════════════════════╤═══════════════════════════════════════════════════════════════════════╤══════════╤════════╤═════════╗
║ Action                       │ File Name             │ Absolute Path                                                         │ Warnings │ Errors │ Success ║
╟──────────────────────────────┼───────────────────────┼───────────────────────────────────────────────────────────────────────┼──────────┼────────┼─────────╢
║ Create component config YAML │ FullWidthCallout.yaml │ /Users/abryrath/Union/Library/craft-components/src/components/configs │ 1        │        │ Y       ║
╟──────────────────────────────┼───────────────────────┼───────────────────────────────────────────────────────────────────────┼──────────┼────────┼─────────╢
║ Create component PHP class   │ FullWidthCallout.php  │ /Users/abryrath/Union/Library/craft-components/src/components         │          │        │ Y       ║
╟──────────────────────────────┼───────────────────────┼───────────────────────────────────────────────────────────────────────┼──────────┼────────┼─────────╢
║ Create embed template        │ fullWidthCallout.twig │ /Users/abryrath/Union/Library/craft-components/src/templates/embed    │          │        │ Y       ║
╟──────────────────────────────┼───────────────────────┼───────────────────────────────────────────────────────────────────────┼──────────┼────────┼─────────╢
║ Create system template       │ fullWidthCallout.twig │ /Users/abryrath/Union/Library/craft-components/src/templates/system   │          │        │ Y       ║
╚══════════════════════════════╧═══════════════════════╧═══════════════════════════════════════════════════════════════════════╧══════════╧════════╧═════════╝

Warnings
╔══════════════════════════════╤══════════════════════════════════════════════════════════════════════════════════════════════════════════════╗
║ Action                       │ Warnings                                                                                                     ║
╟──────────────────────────────┼──────────────────────────────────────────────────────────────────────────────────────────────────────────────╢
║ Create component config YAML │ Empty YAML file has been generated. You must add your own config to the file before installing the component ║
╚══════════════════════════════╧══════════════════════════════════════════════════════════════════════════════════════════════════════════════╝
```
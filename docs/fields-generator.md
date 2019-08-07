# Fields Generator
Use the field generator utility to create scaffoling for a new field.

### Terminology

* Simple Field
  * A simple field is any Craft field type that does not include sub-fields. `Plain Text` and `Asset` are examples of simple fields.
* Complex Field
  * A complex field is a field that contains sub-fields. The only supported complex fields are `Matrix` and `SuperTable`
  

### Usage
To start the generator, run the following command.
```shell
$ php craft components/field/generate [fieldName]
```
* Optional: `fieldName`
  * Must be quoted if `fieldName` is multiple words

This will start an interactive process to create a simple or complex field.

IMPORTANT - Simple fields must be created before they can be added to a complex field or added to a component.


### Example
Simple
```shell
$ php craft components/field/generate

Field Generator
Give the field a name:  HeroTitle
Give the field a handle:  [heroTitl]
Select a field type:  [plainText,lightswitch,supertable,matrix,?]: plainText
Instructions for field: The primary text shown on the hero


Preview

Field Name:
        Testing

Field Handle:
        testing

Field Type:
        testing

Field Instructions:
        The primary text shown on the hero



Proceed? (yes|no) [no]:
```
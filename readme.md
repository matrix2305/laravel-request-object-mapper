## IMPLEMENTATION

***Important: All properties for map must be public***

Extends request class with BaseRequestObjectMapper

```class CreateBannerRequest extends BaseRequestObjectMapper```

Map child object in array 

    #[ArrayChildObjectMap(objectClass: ChildRequestClass::class)]
    public array $items;

Map numeric array

    #[ArrayChildTypeMap(type: ArrayChildType::FLOAT)]
    public array $itemsInArray;

### Laravel Object Validator

If you do before steps you can validate your properties in class by PropertyValidationRules attribute

    #[PropertyValidationRules(rules: 'required|integer', messages: ['required' => 'Id is required field.'])]
    public int $id;

    #[PropertyValidationRules(rules: 'required|string|max:255', messages: ['required' => 'Name is required field.', 'max' => 'Name can contains maximum 255 characters.'])]
    public string $name;

If validation not successfully BaseRequestObjectMapper throws FailedValidationException
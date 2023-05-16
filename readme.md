## IMPLEMENTATION

Extends request class with BaseRequestObjectMapper

```class CreateBannerRequest extends BaseRequestObjectMapper```

Map child object in array 

    #[ArrayChildObjectMap(objectClass: ChildRequestClass::class)]
    public array $items;

Map numeric array

    #[ArrayChildTypeMap(type: ArrayChildType::FLOAT)]
    public array $itemsInArray;
<?php
return [
    'auth' => [
        'login'         => 'admin',
        'password'      => 'admin',
        'session'       => 'onec_session',
    ],
    // Путь для 1С <домен>/exchange_path
    'exchange_path' => 'onec_exchange',
    'setup' => [
        'import_dir'    => storage_path('app/onec'),
        'use_zip'       => false,
        'file_limit'    => 1024 * 1024 * 200,
    ],
    'models' => [
        'group' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiGroup::class, // Класс модель для хранения групп
            'id'        => 'sku', // Поле ИД для ид из 1с в модели
            'parent_id' => 'parent_sku', // Поле для родителя ди из 1с в моедли
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'name' => 'Наименование',
            ],
            // Класс реализации своей логики в ключевых событиях
            'observer'  => '',
        ],
        'product' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiProduct::class,
            'id'        => 'sku', // Поле ИД для ид из 1с в модели
            'parent_id' => 'group_sku', // Поле для родителя ди из 1с в моедли
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'name' => 'Наименование',
                'barcode' => 'Штрихкод',
                'art' => 'Артикул',
		'description' => 'Описание',
            ],
            // Класс для парсинга загруженных изображений
            // Должен быть реализован интерфейс XmlImageParserInterface
            'images'    => \Vitaliy914\OneCApi\Parser\XmlImageParser::class,
            // Класс реализации своей логики в ключевых событиях
            'observer'  => '',
        ],
	'images' => [
  	    'model' => \Vitaliy914\OneCApi\Models\OnecapiImage::class,
	],
        'attribute_values' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiAttributeValue::class,
            'id'        => 'sku', // Поле ИД для ид из 1с в модели
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'name' => 'Наименование',
                'value' => 'Значение',
                ]
        ],
        'property' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiProperty::class,
            'id'        => 'sku', // Поле ИД для ид из 1с в модели
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'name' => 'Наименование',
            ]
        ],
        'property_variants' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiPropertyVariant::class,
            'id'        => 'sku', // Поле ИД для ид из 1с в модели
            'parent_id' => 'property_sku',
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'property_sku' => 'ИдЗначения',
                'name' => 'Значение',
            ]
        ],
        'property_values' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiPropertyValue::class,
            'id'        => 'product_sku', // Поле ИД для ид из 1с в модели
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'property_sku' => 'Ид',
                'property_variant_sku' => 'Значение',
            ]
        ],
        'shops' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiShop::class,
            'id'        => 'shop_sku', // Поле ИД для ид из 1с в модели
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'shop_sku' => 'Ид',
                'name' => 'Название',
            ]
        ],

        'price_type' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiPriceType::class,
            'id'        => 'sku', // Поле ИД для ид из 1с в модели
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'name' => 'Наименование',
                'currency' => 'Валюта',
            ],
            // Класс реализации своей логики в ключевых событиях
            'observer'  => '',
        ],

        // остатки
        'residue' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiProduct::class,
            'id'        => 'sku', // Поле ИД для ид из 1с в модели
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'residue' => 'Количество',
            ],
            'observer'  => '', // только updating и updated
        ],
        'prices' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiPrice::class,
            'id'        => 'product_sku', // Поле ИД для ид из 1с в модели
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'type_sku' => 'ИдТипаЦены',
                'view' => 'Представление',
                'price_per_unit' => 'ЦенаЗаЕдиницу',
                'currency' => 'Валюта',
                'unit' => 'Единица',
                'ratio' => 'Коэффициент',
                'discount' => 'Скидка',
                'price_with_discount' => 'ЦенаСоСкидкой',
            ],
            'observer'  => '', // только updating и updated
        ],
        'leftovers' => [
            'model'     => \Vitaliy914\OneCApi\Models\OnecapiProductInShop::class,
            'id'        => 'product_sku', // Поле ИД для ид из 1с в модели
            'fillable' => [
                // Соответствие полей из 1с и модели
                // Model->field => 1C->field
                'shop_sku' => 'ИдМагазина',
                'count' => 'Количество',
            ],
            'observer'  => '', // только updating и updated
        ]
    ],
];

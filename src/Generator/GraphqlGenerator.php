<?php

namespace Anwoon\BlueprintGraphqlAddon\Generator;

use Blueprint\Contracts\Generator;
use Blueprint\Models\Model;
use Blueprint\Tree;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GraphqlGenerator implements Generator
{

    public function __construct(private $files)
    {
    }

    public function output(Tree $tree): array
    {
        $output = [];
        $disk = Storage::disk('graphql');

        /** @var \Blueprint\Models\Model $model */
        foreach($tree->models() as $model) {
            if (!$disk->exists($model->name())) {
                $disk->makeDirectory($model->name());
            }

            $path = $model->name() .'/type.graphql';
            $data = $this->prepareTypeData($model);
            $content = $this->generateContent($model->name(), $data);
            $disk->put($path, $content);

            $output['created'][] = $path;
        }

        return $output;
    }

    public function generateContent(string $name, array $data): string
    {
        $content = 'type '.$name.' implements Model {';

        foreach ($data['fields'] as $field) {
            $content .= PHP_EOL.'    '.$field['id'].': '.($field['type'] === 'Enum' ? $field['enum'] : $field['type']).($field['required'] ? '!' : '');
        }

        foreach ($data['relationships'] as $relationship) {
            $content .= PHP_EOL.'    '.$relationship['id'].': '.($relationship['isMultiple'] ? '[' : '').$relationship['relation'].'!'.($relationship['isMultiple'] ? ']' : '').' @'.$relationship['type'];
        }

        $content .= PHP_EOL.'}'.PHP_EOL;

        foreach ($data['enums'] as $key => $enums) {
            $content .= PHP_EOL.'enum '.$key. ' {';

            foreach ($enums as $enum) {
                $content .= PHP_EOL.'    '.Str::upper($enum);
            }

            $content .= PHP_EOL.'}'.PHP_EOL;
        }

        return $content;
    }

    public function prepareTypeData(Model $model): array
    {
        $data = [
            'enums' => [],
            'fields' => [],
            'relationships' => []
        ];

        /**
         * @var string $key
         * @var \Blueprint\Models\Column $column
         */
        foreach ($model->columns() as $key => $column) {
            if ($column->dataType() === 'enum') {
                $data['enums'][Str::ucfirst(Str::camel($column->name()))] = $column->attributes();
            }

            $field = [
                'id' => $key,
                'type' => $this->getGraphqlType($column->dataType()),
                'required' => !$column->isNullable(),
            ];

            if ($field['type'] === 'Enum') {
                $field['enum'] = Str::ucfirst(Str::camel($column->name()));
            }

            $data['fields'][] = $field;
        }

        foreach ($model->relationships() as $key => $relationship) {
            foreach ($relationship as $item) {
                if (!Str::contains($item, ':')) {
                    $isMultiple = in_array($key, ['hasMany', 'belongsToMany']);

                    $data['relationships'][] = [
                        'id' => $isMultiple ? Str::plural(Str::camel($item)) : Str::camel($item),
                        'type' => $key,
                        'relation' => $item,
                        'isMultiple' => $isMultiple
                    ];
                }
            }
        }

        return $data;
    }

    public function getGraphqlType(string $type): string
    {
        $graphqlType = 'String';

        switch($type) {
            case 'id':
                $graphqlType = 'ID';
                break;
            case 'enum':
                $graphqlType = 'Enum';
                break;
            case 'timestamp':
                $graphqlType = 'Timestamp';
                break;
            case 'date':
                $graphqlType = 'Date';
                break;
            case 'datetime':
                $graphqlType = 'DateTime';
                break;
            case 'integer':
                $graphqlType = 'Int';
                break;
            case 'boolean':
                $graphqlType = 'Boolean';
                break;
            case 'double':
            case 'float':
                $graphqlType = 'Float';
                break;
        }

        return $graphqlType;
    }

    public function types(): array
    {
        return ['graphql'];
    }
}

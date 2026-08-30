<?php

namespace Teksite\Extralaravel\Routing\Trash;

final class TrashResourceDefinition
{
    /**
     * @return array<string, array{
     *     method: string,
     *     uri: string,
     *     action: string
     * }>
     */
    public static function actions(): array
    {
        return [
            TrashAction::INDEX->value => [
                'method'  => 'GET',
                'uri'     => 'trash',
                'action'  => 'index',
                'binding' => false,

            ],

            TrashAction::REINSTATE->value => [
                'method'  => 'PATCH',
                'uri'     => 'trash/{resource}',
                'action'  => 'reinstate',
                'binding' => true,

            ],

            TrashAction::PRUNE->value => [
                'method'  => 'DELETE',
                'uri'     => 'trash/{resource}',
                'action'  => 'prune',
                'binding' => true,

            ],

            TrashAction::RESTORE->value => [
                'method'  => 'PATCH',
                'uri'     => 'trash',
                'action'  => 'restore',
                'binding' => false,

            ],

            TrashAction::FLUSH->value => [
                'method'  => 'DELETE',
                'uri'     => 'trash',
                'action'  => 'flush',
                'binding' => false,

            ],
        ];
    }

    private function __construct() {}
}

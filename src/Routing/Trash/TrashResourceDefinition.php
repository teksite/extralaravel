<?php

namespace Teksite\Extralaravel\Routing\Trash;

final class TrashResourceDefinition
{
    /**
     * Route definitions indexed by TrashAction value.
     *
     * "binding" marks actions that address one trashed record through the {resource} wildcard.
     */
    private const array ACTIONS = [
        'index'     => [
            'method'  => 'GET',
            'uri'     => 'trash',
            'action'  => 'index',
            'binding' => false,
        ],
        'reinstate' => [
            'method'  => 'PATCH',
            'uri'     => 'trash/{resource}',
            'action'  => 'reinstate',
            'binding' => true,
        ],
        'prune'     => [
            'method'  => 'DELETE',
            'uri'     => 'trash/{resource}',
            'action'  => 'prune',
            'binding' => true,
        ],
        'restore'   => [
            'method'  => 'PATCH',
            'uri'     => 'trash',
            'action'  => 'restore',
            'binding' => false,
        ],
        'flush'     => [
            'method'  => 'DELETE',
            'uri'     => 'trash',
            'action'  => 'flush',
            'binding' => false,
        ],
    ];
    /**
     * @return array<string, array{method: string, uri: string, action: string, binding: bool}>
     */
    public static function actions(): array
    {
        return self::ACTIONS;
    }


    private function __construct() {}
}

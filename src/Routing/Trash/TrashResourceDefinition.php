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
                'method' => 'GET',
                'uri' => 'trash',
                'action' => 'index',
            ],

            TrashAction::REINSTATE->value => [
                'method' => 'PATCH',
                'uri' => 'trash/{id}',
                'action' => 'reinstate',
            ],

            TrashAction::PRUNE->value => [
                'method' => 'DELETE',
                'uri' => 'trash/{id}',
                'action' => 'prune',
            ],

            TrashAction::RESTORE->value => [
                'method' => 'PATCH',
                'uri' => 'trash',
                'action' => 'restore',
            ],

            TrashAction::FLUSH->value => [
                'method' => 'DELETE',
                'uri' => 'trash',
                'action' => 'flush',
            ],
        ];
    }

    private function __construct()
    {
    }
}

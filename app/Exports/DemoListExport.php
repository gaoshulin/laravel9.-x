<?php

namespace App\Exports;

// 导出数据
use Carbon\Carbon;

class DemoListExport extends CollectionExport
{
    public function __construct($data, $params = [])
    {
        parent::__construct($data, $params);
    }

    public function collection()
    {
        $data = $this->data;
        $params = $this->params;

        $list = []; // 导出的数据处理
        $urlData = []; // 需要点击跳转链接的数据
        foreach ($data as $key => $item) {
            $list[] = [
                'id'            => (string) $item['id'],
                'title'         => $item['title'] ?? '',
                'content'       => $item['content'] ?? '',
                'view_more'     => 'view more'
            ];

            $url = config('app.url').'/details/'.$item['id'];
            $urlData[] = [
                'A' => $url,
                'D' => $url,
            ];
        }

        $this->urlData = $urlData;

        return collect($list);
    }

    public function map($row): array
    {
        return [
            $row['id'],
            $row['title'],
            $row['content'],
            $row['view_more'],
        ];
    }

    public function headings(): array
    {
        return [
            [
                __('export.header description')
            ],
            [
                __('export.id'),
                __('export.title'),
                __('export.content'),
                __('export.view more'),
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, 'B' => 20, 'C' => 20, 'D' => 20
        ];
    }

}

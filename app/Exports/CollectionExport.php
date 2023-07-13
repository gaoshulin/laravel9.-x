<?php

namespace App\Exports;

use App\Support\Utils;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 文件导出基类
 */
class CollectionExport implements Responsable, FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    use Exportable;

    protected $data     = [];    // 导出的数据
    protected $params   = [];    // 额外参数
    protected $urlData  = [];    // url跳转参数
    protected $headLen  = 0;     // 表头数组长度
    protected $dataLen  = 0;     // 表格数据内容长度
    protected $rowsLen  = 0;     // 表格数据总长度
    protected $lastRowIndex = '';    // 最后一列单元格

    /**
     * ExportCollection constructor.
     * @param $data
     * @param $params
     */
    public function __construct($data, $params = [])
    {
        // 实例化需要传入要导出的数据
        $this->data     = $data;
        $this->params   = $params;
        $this->dataLen  = count($data); // 数据长度
        $this->headLen  = count($this->headings()); // 表头长度
        $this->rowsLen  = $this->headLen + $this->dataLen;

        // 获取表格的最后一列对应的 index
        $this->lastRowIndex = array_key_last($this->columnWidths());
    }

    /**
     * 返回的数据
     * @return array
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * 将数组转为集合
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = $this->data;

        return collect($data);
    }

    /**
     * 指定excel中每一列的数据字段
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [];
    }

    /**
     * 指定excel的表头
     * @return array
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * 设置列宽
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, 'B' => 20, 'C' => 20, 'D' => 20, 'E' => 20, 'F' => 20, 'G' => 20, 'H' => 20, 'I' => 20,
            'J' => 20, 'K' => 20, 'L' => 20, 'M' => 20, 'N' => 20, 'O' => 20, 'P' => 20, 'Q' => 20, 'R' => 20,
            'S' => 20, 'T' => 20, 'U' => 20, 'V' => 20, 'W' => 20, 'X' => 20, 'Y' => 20, 'Z' => 20,
        ];
    }

    /**
     * 样式设置
     * @param Worksheet $sheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getDefaultRowDimension()->setRowHeight(30); // 设置行高
        $sheet->getStyle('A1:Z'.$this->rowsLen)->getAlignment()->setVertical('center'); // 垂直居中
        $sheet->getStyle('A1:Z'.$this->rowsLen)->applyFromArray(['alignment' => ['horizontal' => 'center']]); // 设置水平居中
        $sheet->getStyle('A1:Z'.$this->rowsLen)->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => '000000']]]); // 字体设置

        // 合并表头第一行,并设置通用的excel表头
        $sheet->mergeCells('A1:'.$this->lastRowIndex.'1');
        // 插入超链接-表头
        $utmUrl = 'https://www.baidu.com';
        $sheet->getCell("A1")->getHyperlink()->setUrl($utmUrl);
        // 设置带有超链接单元的的样式-表头
        $sheet->getStyle('A1:'.$this->lastRowIndex.'1')->applyFromArray(['font' => ['color' => ['rgb' => '0000FF'], 'underline' => 'single']]);

        // Excel 插入公式
//        $sheet->setCellValue("H3","=SUM(G3+100)"); //
//        $sheet->setCellValue("D$i","=SUM(I$i+J$i+K$i+L$i+M$i+N$i)");

        // 处理带有超链接的数据，在每一个导出方法中设置
        $urlData = $this->urlData;
        foreach ($urlData as $key => $items) {
            // 循环获取需要跳转的列和链接
            foreach ($items as $index => $url) {
                $indexNum = $this->headLen + $key + 1;
                // 插入超链接 url
                $sheet->getCell($index.$indexNum)->getHyperlink()->setUrl($url);

                // 设置带有超链接单元的的样式
                $sheet->getStyle($index.$indexNum)->applyFromArray(['font' => ['color' => ['rgb' => '0000FF'], 'underline' => 'single']]);
            }
        }
    }

}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\PatternFill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ComprasExport implements WithMultipleSheets
{
    protected $compras;

    public function __construct($compras)
    {
        $this->compras = $compras;
    }

    public function sheets(): array
    {
        return [
            'Resumen' => new ResumenComprasSheet($this->compras),
            'Detalle' => new DetalleComprasSheet($this->compras),
        ];
    }
}

/**
 * Hoja de Resumen General de Compras
 */
class ResumenComprasSheet implements FromArray, WithHeadings, WithStyles
{
    protected $compras;

    public function __construct($compras)
    {
        $this->compras = $compras;
    }

    public function array(): array
    {
        return $this->compras->map(function($compra) {
            return [
                $compra->created_at->format('d/m/Y H:i'),
                $compra->proveedor ? $compra->proveedor->nombre : 'Sin proveedor',
                $compra->usuario ? $compra->usuario->name : 'Sin usuario',
                $compra->sucursal ? $compra->sucursal->name : 'N/A',
                $compra->detalles->count(),
                $compra->total_estimado,
                $compra->total_real ?? 0,
                ucfirst(str_replace('_', ' ', $compra->estado)),
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Proveedor',
            'Supervisor',
            'Sucursal',
            'Cant. Productos',
            'Total Estimado',
            'Total Real',
            'Estado',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $borderStyle = [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'D0D0D0'],
        ];

        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1565C0']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => [
                    'allBorders' => $borderStyle,
                ]
            ],
            'A' => ['width' => 18],
            'B' => ['width' => 20],
            'C' => ['width' => 20],
            'D' => ['width' => 15],
            'E' => ['width' => 12],
            'F' => ['width' => 15],
            'G' => ['width' => 15],
            'H' => ['width' => 15],
        ];

        // Colorear filas alternas y pares con bordes
        foreach (range(2, $sheet->getHighestRow()) as $row) {
            if ($row % 2 == 0) {
                for ($col = 'A'; $col <= 'H'; $col++) {
                    $sheet->getStyle($col . $row)->getFill()->setFillType('solid')->setStartColor(new Color('F5F5F5'));
                }
            }
            for ($col = 'A'; $col <= 'H'; $col++) {
                $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                if (in_array($col, ['F', 'G', 'H'])) {
                    $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            }
        }

        return $styles;
    }
}

/**
 * Hoja de Detalle de Productos por Compra
 */
class DetalleComprasSheet implements FromArray, WithHeadings, WithStyles
{
    protected $compras;

    public function __construct($compras)
    {
        $this->compras = $compras;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->compras as $compra) {
            // Encabezado de la compra
            $data[] = [
                'COMPRA #' . $compra->id,
                '',
                '',
                '',
                '',
                '',
            ];

            $data[] = [
                'Fecha: ' . $compra->created_at->format('d/m/Y H:i'),
                'Proveedor: ' . ($compra->proveedor ? $compra->proveedor->nombre : 'Sin proveedor'),
                'Supervisor: ' . ($compra->usuario ? $compra->usuario->name : 'Sin usuario'),
                'Sucursal: ' . ($compra->sucursal ? $compra->sucursal->name : 'N/A'),
                'Estado: ' . ucfirst(str_replace('_', ' ', $compra->estado)),
                '',
            ];

            // Encabezados de productos
            $data[] = [
                'Producto',
                'Cantidad Solicitada',
                'Cantidad Recibida',
                'Cantidad Pendiente',
                'Precio Compra',
                'Subtotal',
            ];

            // Detalle de productos
            if ($compra->detalles->count() > 0) {
                foreach ($compra->detalles as $detalle) {
                    $pendiente = $detalle->cantidad_solicitada - $detalle->cantidad_recibida;
                    $data[] = [
                        $detalle->producto ? $detalle->producto->nombre : 'Producto eliminado',
                        $detalle->cantidad_solicitada,
                        $detalle->cantidad_recibida,
                        $pendiente,
                        $detalle->precio_compra,
                        $detalle->subtotal,
                    ];
                }
            } else {
                $data[] = [
                    'Sin productos',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];
            }

            // Total de la compra
            $totalDetalle = $compra->detalles->sum('subtotal');
            $data[] = [
                'TOTAL:',
                '',
                '',
                '',
                'Est: $' . number_format($compra->total_estimado, 2),
                'Real: $' . number_format($compra->total_real ?? 0, 2),
            ];

            // Línea en blanco
            $data[] = ['', '', '', '', '', ''];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Cantidad Solicitada',
            'Cantidad Recibida',
            'Cantidad Pendiente',
            'Precio Compra',
            'Subtotal',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $borderStyle = [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'D0D0D0'],
        ];

        $styles = [
            'A' => ['width' => 25],
            'B' => ['width' => 18],
            'C' => ['width' => 18],
            'D' => ['width' => 18],
            'E' => ['width' => 15],
            'F' => ['width' => 15],
        ];

        // Aplicar estilos condicionales a todas las filas
        $highestRow = $sheet->getHighestRow();
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            
            // Detectar encabezados de compra
            if (stripos($cellValue, 'COMPRA #') !== false) {
                // Encabezado de sección - fondo azul oscuro
                for ($col = 'A'; $col <= 'F'; $col++) {
                    $style = $sheet->getStyle($col . $row);
                    $style->getFill()->setFillType('solid')->setStartColor(new Color('455A64'));
                    $style->getFont()->setBold(true)->setColor(new Color('FFFFFF'))->setSize(12);
                    $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                }
            } elseif ($cellValue === 'Producto' || ($row > 1 && stripos($sheet->getCell('A' . ($row - 1))->getValue(), 'COMPRA #') !== false)) {
                // Info de la compra o encabezados de tabla
                if ($cellValue === 'Producto') {
                    // Encabezados de tabla de productos - fondo azul claro
                    for ($col = 'A'; $col <= 'F'; $col++) {
                        $style = $sheet->getStyle($col . $row);
                        $style->getFill()->setFillType('solid')->setStartColor(new Color('E3F2FD'));
                        $style->getFont()->setBold(true)->setColor(new Color('1565C0'));
                        $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                    }
                } else {
                    // Filas de info de la compra
                    for ($col = 'A'; $col <= 'F'; $col++) {
                        $style = $sheet->getStyle($col . $row);
                        $style->getFill()->setFillType('solid')->setStartColor(new Color('F5F5F5'));
                        $style->getFont()->setItalic(true);
                        $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                    }
                }
            } elseif (stripos($cellValue, 'TOTAL:') !== false) {
                // Fila de total - fondo verde
                for ($col = 'A'; $col <= 'F'; $col++) {
                    $style = $sheet->getStyle($col . $row);
                    $style->getFill()->setFillType('solid')->setStartColor(new Color('C8E6C9'));
                    $style->getFont()->setBold(true)->setColor(new Color('1B5E20'));
                    $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                }
            } else {
                // Filas normales de datos - alternancia de colores
                if ($row % 2 == 0 && $cellValue !== '') {
                    for ($col = 'A'; $col <= 'F'; $col++) {
                        $sheet->getStyle($col . $row)->getFill()->setFillType('solid')->setStartColor(new Color('FAFAFA'));
                    }
                }
                for ($col = 'A'; $col <= 'F'; $col++) {
                    $style = $sheet->getStyle($col . $row);
                    $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                    if (in_array($col, ['B', 'C', 'D', 'E', 'F'])) {
                        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                }
            }
        }

        return $styles;
    }
}
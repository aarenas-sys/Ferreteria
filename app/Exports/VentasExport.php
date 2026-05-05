<?php

namespace App\Exports;

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

class VentasExport implements WithMultipleSheets
{
    protected $ventas;

    public function __construct($ventas)
    {
        $this->ventas = $ventas;
    }

    public function sheets(): array
    {
        return [
            'Resumen' => new ResumenVentasSheet($this->ventas),
            'Detalle' => new DetalleVentasSheet($this->ventas),
        ];
    }
}

/**
 * Hoja de Resumen General de Ventas
 */
class ResumenVentasSheet implements FromArray, WithHeadings, WithStyles
{
    protected $ventas;

    public function __construct($ventas)
    {
        $this->ventas = $ventas;
    }

    public function array(): array
    {
        return $this->ventas->map(function($venta) {
            return [
                $venta->fecha_venta->format('d/m/Y H:i'),
                $venta->cliente ? $venta->cliente->nombre_completo : 'Cliente General',
                $venta->usuario ? $venta->usuario->name : 'Sin usuario',
                $venta->sucursal ? $venta->sucursal->name : 'N/A',
                $venta->detalles->count(),
                ucfirst($venta->tipo_venta),
                $venta->total,
                ucfirst($venta->estado),
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Cliente',
            'Cajero',
            'Sucursal',
            'Cant. Productos',
            'Tipo Venta',
            'Total',
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
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E7D32']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => [
                    'allBorders' => $borderStyle,
                ]
            ],
            'A' => ['width' => 18],
            'B' => ['width' => 25],
            'C' => ['width' => 18],
            'D' => ['width' => 15],
            'E' => ['width' => 12],
            'F' => ['width' => 12],
            'G' => ['width' => 15],
            'H' => ['width' => 15],
        ];

        // Colorear filas alternas y pares
        foreach (range(2, $sheet->getHighestRow()) as $row) {
            if ($row % 2 == 0) {
                for ($col = 'A'; $col <= 'H'; $col++) {
                    $sheet->getStyle($col . $row)->getFill()->setFillType('solid')->setStartColor(new Color('F1F8F4'));
                }
            }
            for ($col = 'A'; $col <= 'H'; $col++) {
                $style = $sheet->getStyle($col . $row);
                $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                if (in_array($col, ['E', 'G', 'H'])) {
                    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            }
        }

        return $styles;
    }
}

/**
 * Hoja de Detalle de Productos por Venta
 */
class DetalleVentasSheet implements FromArray, WithHeadings, WithStyles
{
    protected $ventas;

    public function __construct($ventas)
    {
        $this->ventas = $ventas;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->ventas as $venta) {
            // Encabezado de la venta
            $data[] = [
                'VENTA #' . $venta->id,
                '',
                '',
                '',
                '',
                '',
            ];

            $data[] = [
                'Fecha: ' . $venta->fecha_venta->format('d/m/Y H:i'),
                'Cliente: ' . ($venta->cliente ? $venta->cliente->nombre_completo : 'Cliente General'),
                'Cajero: ' . ($venta->usuario ? $venta->usuario->name : 'Sin usuario'),
                'Sucursal: ' . ($venta->sucursal ? $venta->sucursal->name : 'N/A'),
                'Tipo: ' . ucfirst($venta->tipo_venta),
                'Estado: ' . ucfirst($venta->estado),
            ];

            // Encabezados de productos
            $data[] = [
                'Código',
                'Producto',
                'Cantidad',
                'Precio Unitario',
                'Subtotal',
                '',
            ];

            // Detalle de productos
            if ($venta->detalles && $venta->detalles->count() > 0) {
                foreach ($venta->detalles as $detalle) {
                    $subtotal = $detalle->cantidad * $detalle->precio_unitario;
                    $data[] = [
                        $detalle->producto ? $detalle->producto->codigo : 'N/A',
                        $detalle->producto ? $detalle->producto->nombre : 'Producto eliminado',
                        $detalle->cantidad,
                        $detalle->precio_unitario,
                        $subtotal,
                        '',
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

            // Total de la venta
            $totalProductos = $venta->detalles && $venta->detalles->count() > 0 
                ? $venta->detalles->sum(function($d) { return $d->cantidad * $d->precio_unitario; })
                : 0;
            
            $data[] = [
                'TOTAL:',
                '',
                '',
                '',
                'Total Venta: $' . number_format($venta->total, 2),
                '',
            ];

            // Línea en blanco
            $data[] = ['', '', '', '', '', ''];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Producto',
            'Cantidad',
            'Precio Unitario',
            'Subtotal',
            '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $borderStyle = [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'D0D0D0'],
        ];

        $styles = [
            'A' => ['width' => 15],
            'B' => ['width' => 30],
            'C' => ['width' => 12],
            'D' => ['width' => 18],
            'E' => ['width' => 15],
            'F' => ['width' => 5],
        ];

        // Aplicar estilos a encabezados y datos
        $highestRow = $sheet->getHighestRow();
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            
            // Detectar encabezados de productos
            if (stripos($cellValue, 'VENTA #') !== false) {
                // Encabezado de sección VENTA - fondo oscuro
                for ($col = 'A'; $col <= 'F'; $col++) {
                    $style = $sheet->getStyle($col . $row);
                    $style->getFill()->setFillType('solid')->setStartColor(new Color('455A64'));
                    $style->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
                    $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                }
            } elseif ($cellValue === 'Código' || (stripos($cellValue, 'Fecha:') !== false || stripos($cellValue, 'Cliente:') !== false || stripos($cellValue, 'Cajero:') !== false)) {
                // Encabezados de tabla de productos
                if ($cellValue === 'Código') {
                    for ($col = 'A'; $col <= 'F'; $col++) {
                        $style = $sheet->getStyle($col . $row);
                        $style->getFill()->setFillType('solid')->setStartColor(new Color('E3F2FD'));
                        $style->getFont()->setBold(true)->setColor(new Color('1565C0'));
                        $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                    }
                } else {
                    // Filas de info
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
                // Otras filas - datos normales
                if ($row % 2 == 0 && $cellValue !== '') {
                    for ($col = 'A'; $col <= 'F'; $col++) {
                        $sheet->getStyle($col . $row)->getFill()->setFillType('solid')->setStartColor(new Color('FAFAFA'));
                    }
                }
                for ($col = 'A'; $col <= 'F'; $col++) {
                    $style = $sheet->getStyle($col . $row);
                    $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                    if (in_array($col, ['C', 'D', 'E'])) {
                        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                }
            }
        }

        return $styles;
    }
}
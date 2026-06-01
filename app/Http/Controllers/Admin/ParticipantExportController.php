<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ParticipantExportController extends Controller
{
    public function csv(): StreamedResponse
    {
        $filename = 'participantes-ianus-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ($this->rows() as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function xlsx(): BinaryFileResponse|Response
    {
        if (! class_exists(ZipArchive::class)) {
            return response('La extension zip de PHP es necesaria para exportar XLSX.', 500);
        }

        $filename = 'participantes-ianus-'.now()->format('Ymd-His').'.xlsx';
        $path = storage_path('app/'.$filename);

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($this->rows()));
        $zip->close();

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function rows(): array
    {
        $rows = [[
            'Fecha/hora',
            'Nombre y apellido',
            'Documento',
            'Mail',
            'Celular',
            'Institucion/cargo',
            'Set',
            'Respuestas correctas',
            'Tiempo final segundos',
            'Tiempo final mm:ss',
            'Estado',
            'Posible duplicado',
        ]];

        Attempt::query()
            ->with(['participant', 'questionSet'])
            ->latest()
            ->chunk(200, function ($attempts) use (&$rows): void {
                foreach ($attempts as $attempt) {
                    $rows[] = [
                        $attempt->created_at?->format('Y-m-d H:i:s'),
                        $attempt->participant->full_name,
                        $attempt->participant->document_number,
                        $attempt->participant->email,
                        $attempt->participant->phone,
                        $attempt->participant->institution_role,
                        $attempt->questionSet->name,
                        $attempt->correct_answers_count,
                        $attempt->total_time_seconds,
                        $attempt->formattedTime(),
                        $attempt->status,
                        $attempt->duplicate_flag ? 'Si' : 'No',
                    ];
                }
            });

        return $rows;
    }

    private function sheetXml(array $rows): string
    {
        $xmlRows = [];

        foreach ($rows as $rowIndex => $row) {
            $cells = [];
            foreach (array_values($row) as $columnIndex => $value) {
                $cells[] = sprintf(
                    '<c r="%s%d" t="inlineStr"><is><t>%s</t></is></c>',
                    $this->columnName($columnIndex + 1),
                    $rowIndex + 1,
                    htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8')
                );
            }

            $xmlRows[] = sprintf('<row r="%d">%s</row>', $rowIndex + 1, implode('', $cells));
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.implode('', $xmlRows).'</sheetData>'
            .'</worksheet>';
    }

    private function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Participantes" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>';
    }
}

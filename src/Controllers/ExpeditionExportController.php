<?php
/**
 * 遠征エクスポートコントローラー
 * Excel（4シート）およびPDF（印刷用HTML）出力
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExpeditionExportController
{
    public function __construct()
    {
        Auth::requireAuth();
    }

    /**
     * Excel出力（参加者一覧・チーム分け・車割・アレルギー一覧の4シート）
     * GET /api/expeditions/{id}/export/xlsx
     */
    public function xlsx(array $params): void
    {
        $expedition = Expedition::findById((int)$params['id']);
        if (!$expedition) Response::error('遠征が見つかりません', 404);

        $participants = ExpeditionParticipant::findByExpedition((int)$params['id']);
        $teams        = ExpeditionTeam::findByExpedition((int)$params['id']);
        $cars         = ExpeditionCar::findByExpedition((int)$params['id']);

        $spreadsheet = new Spreadsheet();

        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('参加者一覧');
        $this->buildParticipantsSheet($sheet1, $expedition, $participants);

        $sheet2 = $spreadsheet->createSheet(1);
        $sheet2->setTitle('チーム分け');
        $this->buildTeamsSheet($sheet2, $expedition, $teams);

        $sheet3 = $spreadsheet->createSheet(2);
        $sheet3->setTitle('車割');
        $this->buildCarsSheet($sheet3, $expedition, $cars);

        $sheet4 = $spreadsheet->createSheet(3);
        $sheet4->setTitle('アレルギー一覧');
        $this->buildAllergySheet($sheet4, $expedition, $participants);

        $spreadsheet->setActiveSheetIndex(0);

        $name     = preg_replace('/[\\\\\/:\*\?"<>\|]/', '_', $expedition['name']);
        $filename = '遠征資料_' . $name . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    /**
     * PDF出力（印刷用HTML）
     * GET /api/expeditions/{id}/export/pdf
     */
    public function pdf(array $params): void
    {
        $expedition = Expedition::findById((int)$params['id']);
        if (!$expedition) Response::error('遠征が見つかりません', 404);

        $participants = ExpeditionParticipant::findByExpedition((int)$params['id']);
        $teams        = ExpeditionTeam::findByExpedition((int)$params['id']);
        $cars         = ExpeditionCar::findByExpedition((int)$params['id']);

        header('Content-Type: text/html; charset=utf-8');
        echo $this->generatePdfHtml($expedition, $participants, $teams, $cars);
        exit;
    }

    // ==================== 内部ヘルパー ====================

    /**
     * 学年性別ラベル取得（10月引退ルール対応）
     */
    private function gradeGenderLabel($grade, ?string $gender): string
    {
        if ($grade === null && $gender === null) return '';
        if ((int)$grade === 0) {
            if ($gender === 'male')   return 'OB';
            if ($gender === 'female') return 'OG';
            return 'OB/OG';
        }
        $month = (int)date('n');
        if (((int)$grade === 3) && ($month >= 10 || $month <= 3)) {
            if ($gender === 'male')   return 'OB';
            if ($gender === 'female') return 'OG';
        }
        $g = $gender === 'male' ? '男' : ($gender === 'female' ? '女' : '');
        return ($grade ? (string)$grade : '') . $g;
    }

    /**
     * ヘッダー行スタイル適用
     */
    private function headerStyle(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    /**
     * 罫線スタイル適用
     */
    private function borderStyle(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);
    }

    // ==================== Excel シート構築 ====================

    /**
     * シート1: 参加者一覧
     */
    private function buildParticipantsSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $expedition,
        array $participants
    ): void {
        // タイトル
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', '参加者一覧　' . $expedition['name']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        // ヘッダー
        $cols = ['A' => 'No.', 'B' => '氏名', 'C' => 'フリガナ', 'D' => '学年性別', 'E' => '前泊', 'F' => '昼食', 'G' => 'アレルギー', 'H' => '住所'];
        foreach ($cols as $col => $label) {
            $sheet->setCellValue($col . '2', $label);
        }
        $this->headerStyle($sheet, 'A2:H2');

        // 列幅
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(7);
        $sheet->getColumnDimension('F')->setWidth(7);
        $sheet->getColumnDimension('G')->setWidth(35);
        $sheet->getColumnDimension('H')->setWidth(40);

        // データ行
        $row = 3;
        foreach ($participants as $i => $p) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $p['name_kanji'] ?? '');
            $sheet->setCellValue('C' . $row, $p['name_kana']  ?? '');
            $sheet->setCellValue('D' . $row, $this->gradeGenderLabel($p['grade'] ?? null, $p['gender'] ?? null));
            $sheet->setCellValue('E' . $row, $p['pre_night'] ? '○' : '');
            $sheet->setCellValue('F' . $row, $p['lunch']     ? '○' : '');
            $sheet->setCellValue('G' . $row, $p['allergy']   ?? '');
            if (!empty($p['allergy'])) {
                $sheet->getStyle('G' . $row)->getFont()->setColor(new Color('FFCC0000'));
            }
            $sheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $sheet->setCellValue('H' . $row, $p['address'] ?? '');
            $sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        if ($row > 3) {
            $this->borderStyle($sheet, 'A2:H' . ($row - 1));
            $sheet->getStyle('A3:F' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // 合計行
        $sheet->setCellValue('A' . $row, '合計');
        $sheet->setCellValue('B' . $row, count($participants) . '名');
        $preCount   = count(array_filter($participants, fn($p) => $p['pre_night']));
        $lunchCount = count(array_filter($participants, fn($p) => $p['lunch']));
        $sheet->setCellValue('E' . $row, $preCount   . '名');
        $sheet->setCellValue('F' . $row, $lunchCount . '名');
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
    }

    /**
     * シート2: チーム分け
     */
    private function buildTeamsSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $expedition,
        array $teams
    ): void {
        // A: チーム名、B: 男氏名、C: 女氏名
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'チーム分け　' . $expedition['name']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);

        $sheet->setCellValue('A2', 'チーム');
        $sheet->setCellValue('B2', '男性');
        $sheet->setCellValue('C2', '女性');
        $this->headerStyle($sheet, 'A2:C2');

        $row = 3;
        foreach ($teams as $team) {
            $members  = $team['members'] ?? [];
            $males    = array_values(array_filter($members, fn($m) => ($m['gender'] ?? '') === 'male'));
            $females  = array_values(array_filter($members, fn($m) => ($m['gender'] ?? '') !== 'male'));
            $rowCount = max(1, count($males), count($females));

            $sheet->setCellValue('A' . $row, $team['name']);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EBF3FB');

            if ($rowCount > 1) {
                $sheet->mergeCells('A' . $row . ':A' . ($row + $rowCount - 1));
                $sheet->getStyle('A' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }

            if (empty($males) && empty($females)) {
                $sheet->setCellValue('B' . $row, '（メンバーなし）');
                $sheet->getStyle('B' . $row)->getFont()->setColor(new Color('FF888888'))->setItalic(true);
            } else {
                for ($i = 0; $i < $rowCount; $i++) {
                    $r = $row + $i;
                    if (isset($males[$i])) {
                        $sheet->setCellValue('B' . $r, $males[$i]['name_kanji'] ?? '');
                    }
                    if (isset($females[$i])) {
                        $sheet->setCellValue('C' . $r, $females[$i]['name_kanji'] ?? '');
                    }
                }
            }

            $row += $rowCount;
        }

        if ($row > 3) {
            $this->borderStyle($sheet, 'A2:C' . ($row - 1));
        }
    }

    /**
     * シート3: 車割
     */
    private function buildCarsSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $expedition,
        array $cars
    ): void {
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', '車割　' . $expedition['name']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(18);

        foreach (['A' => '車名', 'B' => 'No.', 'C' => '役割', 'D' => '氏名'] as $col => $label) {
            $sheet->setCellValue($col . '2', $label);
        }
        $this->headerStyle($sheet, 'A2:D2');

        $roleLabel = ['driver' => 'ドライバー', 'sub_driver' => 'サブドライバー', 'passenger' => '乗客'];

        $row = 3;
        foreach ($cars as $car) {
            $members = $car['car_members'] ?? [];
            $count   = count($members);

            $sheet->setCellValue('A' . $row, $car['name']);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EBF3FB');

            if ($count > 1) {
                $sheet->mergeCells('A' . $row . ':A' . ($row + $count - 1));
                $sheet->getStyle('A' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }

            if ($count === 0) {
                $sheet->setCellValue('C' . $row, '（乗員なし）');
                $sheet->getStyle('C' . $row)->getFont()->setColor(new Color('FF888888'))->setItalic(true);
                $row++;
            } else {
                foreach ($members as $j => $m) {
                    $sheet->setCellValue('B' . $row, $j + 1);
                    $sheet->setCellValue('C' . $row, $roleLabel[$m['role']] ?? $m['role']);
                    $sheet->setCellValue('D' . $row, $m['name_kanji'] ?? '');
                    $row++;
                }
            }
        }

        if ($row > 3) {
            $this->borderStyle($sheet, 'A2:D' . ($row - 1));
            $sheet->getStyle('B3:C' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }

    /**
     * シート4: アレルギー一覧
     */
    private function buildAllergySheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $expedition,
        array $participants
    ): void {
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'アレルギー一覧　' . $expedition['name']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(40);

        foreach (['A' => 'No.', 'B' => '氏名', 'C' => '学年性別', 'D' => 'アレルギー内容'] as $col => $label) {
            $sheet->setCellValue($col . '2', $label);
        }
        $this->headerStyle($sheet, 'A2:D2');

        $allergies = array_values(array_filter($participants, fn($p) => !empty($p['allergy'])));

        if (empty($allergies)) {
            $sheet->mergeCells('A3:D3');
            $sheet->setCellValue('A3', 'アレルギーのある参加者はいません');
            $sheet->getStyle('A3')->getFont()->setItalic(true)->setColor(new Color('FF888888'));
            return;
        }

        $row = 3;
        foreach ($allergies as $i => $p) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $p['name_kanji'] ?? '');
            $sheet->setCellValue('C' . $row, $this->gradeGenderLabel($p['grade'] ?? null, $p['gender'] ?? null));
            $sheet->setCellValue('D' . $row, $p['allergy'] ?? '');
            $sheet->getStyle('D' . $row)->getFont()->setColor(new Color('FFCC0000'));
            $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        $this->borderStyle($sheet, 'A2:D' . ($row - 1));
        $sheet->getStyle('A3:C' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // ==================== PDF HTML 生成 ====================

    private function generatePdfHtml(array $expedition, array $participants, array $teams, array $cars): string
    {
        $h  = fn($s) => htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
        $name = $h($expedition['name']);
        $allergies = array_values(array_filter($participants, fn($p) => !empty($p['allergy'])));

        // ===== 参加者一覧テーブル =====
        $preCount   = count(array_filter($participants, fn($p) => $p['pre_night']));
        $lunchCount = count(array_filter($participants, fn($p) => $p['lunch']));
        $pRows = '';
        foreach ($participants as $i => $p) {
            $allergy = $p['allergy'] ? '<span class="allergy">' . $h($p['allergy']) . '</span>' : '';
            $pRows .= '<tr>'
                . '<td class="center">' . ($i + 1) . '</td>'
                . '<td>' . $h($p['name_kanji']) . '</td>'
                . '<td>' . $h($p['name_kana']) . '</td>'
                . '<td class="center">' . $h($this->gradeGenderLabel($p['grade'] ?? null, $p['gender'] ?? null)) . '</td>'
                . '<td class="center">' . ($p['pre_night'] ? '○' : '') . '</td>'
                . '<td class="center">' . ($p['lunch']     ? '○' : '') . '</td>'
                . '<td>' . $allergy . '</td>'
                . '<td>' . $h($p['address'] ?? '') . '</td>'
                . '</tr>';
        }
        $pRows .= '<tr class="total-row">'
            . '<td colspan="2" class="center"><strong>合計 ' . count($participants) . '名</strong></td>'
            . '<td colspan="2"></td>'
            . '<td class="center"><strong>' . $preCount   . '名</strong></td>'
            . '<td class="center"><strong>' . $lunchCount . '名</strong></td>'
            . '<td></td><td></td></tr>';

        // ===== チーム分けテーブル（男女2列） =====
        $teamRows = '';
        foreach ($teams as $team) {
            $members  = $team['members'] ?? [];
            $males    = array_values(array_filter($members, fn($m) => ($m['gender'] ?? '') === 'male'));
            $females  = array_values(array_filter($members, fn($m) => ($m['gender'] ?? '') !== 'male'));
            $rowCount = max(1, count($males), count($females));

            if (empty($males) && empty($females)) {
                $teamRows .= '<tr>'
                    . '<td rowspan="1" class="team-cell">' . $h($team['name']) . '</td>'
                    . '<td class="gray">（メンバーなし）</td><td></td></tr>';
            } else {
                for ($i = 0; $i < $rowCount; $i++) {
                    $mName = isset($males[$i])   ? $h($males[$i]['name_kanji']   ?? '') : '';
                    $fName = isset($females[$i]) ? $h($females[$i]['name_kanji'] ?? '') : '';
                    $teamRows .= '<tr>'
                        . ($i === 0 ? '<td rowspan="' . $rowCount . '" class="team-cell">' . $h($team['name']) . '</td>' : '')
                        . '<td>' . $mName . '</td>'
                        . '<td>' . $fName . '</td>'
                        . '</tr>';
                }
            }
        }

        // ===== 車割テーブル =====
        $carRows   = '';
        $roleLabel = ['driver' => 'ドライバー', 'sub_driver' => 'サブドライバー', 'passenger' => '乗客'];
        foreach ($cars as $car) {
            $members = $car['car_members'] ?? [];
            $count   = max(1, count($members));
            $first   = true;
            if (empty($members)) {
                $carRows .= '<tr>'
                    . '<td rowspan="1" class="team-cell">' . $h($car['name']) . '</td>'
                    . '<td></td><td></td><td class="gray">（乗員なし）</td></tr>';
            } else {
                foreach ($members as $j => $m) {
                    $carRows .= '<tr>'
                        . ($first ? '<td rowspan="' . $count . '" class="team-cell">' . $h($car['name']) . '</td>' : '')
                        . '<td class="center">' . ($j + 1) . '</td>'
                        . '<td class="center">' . $h($roleLabel[$m['role']] ?? $m['role']) . '</td>'
                        . '<td>' . $h($m['name_kanji']) . '</td>'
                        . '</tr>';
                    $first = false;
                }
            }
        }

        // ===== アレルギー一覧テーブル =====
        $aRows = '';
        if (empty($allergies)) {
            $aRows = '<tr><td colspan="4" class="gray center">アレルギーのある参加者はいません</td></tr>';
        } else {
            foreach ($allergies as $i => $p) {
                $aRows .= '<tr>'
                    . '<td class="center">' . ($i + 1) . '</td>'
                    . '<td>' . $h($p['name_kanji']) . '</td>'
                    . '<td class="center">' . $h($this->gradeGenderLabel($p['grade'] ?? null, $p['gender'] ?? null)) . '</td>'
                    . '<td class="allergy">' . $h($p['allergy']) . '</td>'
                    . '</tr>';
            }
        }

        return '<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>' . $name . ' - 遠征資料</title>
<style>
  body { font-family: "Hiragino Sans", "Yu Gothic", "Meiryo", sans-serif; font-size: 12px; margin: 20px; color: #222; }
  h1 { font-size: 18px; text-align: center; margin-bottom: 4px; }
  h2 { font-size: 14px; margin: 28px 0 6px; border-bottom: 2px solid #334; padding-bottom: 4px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  th, td { border: 1px solid #bbb; padding: 5px 8px; font-size: 11px; }
  th { background: #d9e1f2; font-weight: bold; text-align: center; }
  .center { text-align: center; }
  .team-cell { background: #ebf3fb; font-weight: bold; vertical-align: middle; }
  .allergy { color: #c00; }
  .gray { color: #888; font-style: italic; }
  .total-row { background: #fff2cc; font-weight: bold; }
  .no-print { margin-bottom: 16px; }
  @media print {
    .no-print { display: none; }
    h2 { page-break-before: auto; }
    .page-break { page-break-before: always; }
  }
</style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()" style="padding:6px 16px;margin-right:8px;">印刷 / PDF保存</button>
  <button onclick="window.close()" style="padding:6px 16px;">閉じる</button>
</div>

<h1>' . $name . ' 遠征資料</h1>

<h2>1. 参加者一覧（' . count($participants) . '名）</h2>
<table>
  <thead><tr><th>No.</th><th>氏名</th><th>フリガナ</th><th>学年性別</th><th>前泊</th><th>昼食</th><th>アレルギー</th><th>住所</th></tr></thead>
  <tbody>' . $pRows . '</tbody>
</table>

<h2 class="page-break">2. チーム分け</h2>
<table>
  <thead>
    <tr><th>チーム</th><th>男性</th><th>女性</th></tr>
  </thead>
  <tbody>' . $teamRows . '</tbody>
</table>

<h2>3. 車割</h2>
<table>
  <thead><tr><th>車名</th><th>No.</th><th>役割</th><th>氏名</th></tr></thead>
  <tbody>' . $carRows . '</tbody>
</table>

<h2>4. アレルギー一覧（' . count($allergies) . '名）</h2>
<table>
  <thead><tr><th>No.</th><th>氏名</th><th>学年性別</th><th>アレルギー内容</th></tr></thead>
  <tbody>' . $aRows . '</tbody>
</table>
</body>
</html>';
    }
}

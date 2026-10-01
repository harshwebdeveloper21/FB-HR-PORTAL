<?php
$file = 'd:/xampp/htdocs/HR/Hr-Korvia/app/Controllers/api/AttendanceController.php';
$content = file_get_contents($file);

$lines = explode("\n", $content);
$inLoop = false;
$inExportExcel = false;

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];

    if (strpos($line, 'public function exportExcel') !== false) {
        $inExportExcel = true;
    }

    if ($inExportExcel && preg_match('/foreach \(\$users as \$u\)/', $line)) {
        $inLoop = true;
    }

    if ($inLoop) {
        if (strpos($line, 'A1') !== false && strpos($line, 'A1:') === false) $lines[$i] = str_replace("'A1'", "'A' . (\$rowOffset + 1)", $lines[$i]);
        if (strpos($line, 'A1:{$lastColLet}1') !== false) $lines[$i] = str_replace('A1:{$lastColLet}1', '"A" . ($rowOffset + 1) . ":{$lastColLet}" . ($rowOffset + 1)', $lines[$i]);
        if (strpos($line, 'getRowDimension(1)') !== false) $lines[$i] = str_replace('getRowDimension(1)', 'getRowDimension($rowOffset + 1)', $lines[$i]);
        
        if (strpos($line, 'A2') !== false && strpos($line, 'A2:') === false) $lines[$i] = str_replace("'A2'", "'A' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'B2') !== false) $lines[$i] = str_replace("'B2'", "'B' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'C2') !== false) $lines[$i] = str_replace("'C2'", "'C' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'D2') !== false) $lines[$i] = str_replace("'D2'", "'D' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'E2') !== false) $lines[$i] = str_replace("'E2'", "'E' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'F2') !== false) $lines[$i] = str_replace("'F2'", "'F' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'G2') !== false) $lines[$i] = str_replace("'G2'", "'G' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'H2') !== false) $lines[$i] = str_replace("'H2'", "'H' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'I2') !== false) $lines[$i] = str_replace("'I2'", "'I' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'J2') !== false) $lines[$i] = str_replace("'J2'", "'J' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'K2') !== false) $lines[$i] = str_replace("'K2'", "'K' . (\$rowOffset + 2)", $lines[$i]);
        if (strpos($line, 'A2:K2') !== false) $lines[$i] = str_replace("'A2:K2'", '"A" . ($rowOffset + 2) . ":K" . ($rowOffset + 2)', $lines[$i]);
        if (strpos($line, 'getRowDimension(2)') !== false) $lines[$i] = str_replace('getRowDimension(2)', 'getRowDimension($rowOffset + 2)', $lines[$i]);
        
        if (strpos($line, 'A3') !== false && strpos($line, 'A3:') === false) $lines[$i] = str_replace("'A3'", "'A' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'B3') !== false) $lines[$i] = str_replace("'B3'", "'B' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'C3') !== false) $lines[$i] = str_replace("'C3'", "'C' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'D3') !== false) $lines[$i] = str_replace("'D3'", "'D' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'E3') !== false) $lines[$i] = str_replace("'E3'", "'E' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'F3') !== false) $lines[$i] = str_replace("'F3'", "'F' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'G3') !== false) $lines[$i] = str_replace("'G3'", "'G' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'H3') !== false) $lines[$i] = str_replace("'H3'", "'H' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'I3') !== false) $lines[$i] = str_replace("'I3'", "'I' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'J3') !== false) $lines[$i] = str_replace("'J3'", "'J' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'K3') !== false) $lines[$i] = str_replace("'K3'", "'K' . (\$rowOffset + 3)", $lines[$i]);
        if (strpos($line, 'A3:K3') !== false) $lines[$i] = str_replace("'A3:K3'", '"A" . ($rowOffset + 3) . ":K" . ($rowOffset + 3)', $lines[$i]);
        if (strpos($line, 'getRowDimension(3)') !== false) $lines[$i] = str_replace('getRowDimension(3)', 'getRowDimension($rowOffset + 3)', $lines[$i]);
        
        if (strpos($line, 'getRowDimension(4)') !== false) $lines[$i] = str_replace('getRowDimension(4)', 'getRowDimension($rowOffset + 4)', $lines[$i]);
        
        if (strpos($line, 'A5') !== false && strpos($line, 'A5:') === false) $lines[$i] = str_replace("'A5'", "'A' . (\$rowOffset + 5)", $lines[$i]);
        if (strpos($line, 'B5') !== false) $lines[$i] = str_replace("'B5'", "'B' . (\$rowOffset + 5)", $lines[$i]);
        if (strpos($line, 'getRowDimension(5)') !== false) $lines[$i] = str_replace('getRowDimension(5)', 'getRowDimension($rowOffset + 5)', $lines[$i]);
        if (strpos($line, 'A5:A13') !== false) $lines[$i] = str_replace('"A5:A13"', '"A" . ($rowOffset + 5) . ":A" . ($rowOffset + 13)', $lines[$i]);
        
        if (strpos($line, 'A6') !== false && strpos($line, 'A6:') === false) $lines[$i] = str_replace("'A6'", "'A' . (\$rowOffset + 6)", $lines[$i]);
        if (strpos($line, 'B6') !== false) $lines[$i] = str_replace("'B6'", "'B' . (\$rowOffset + 6)", $lines[$i]);
        if (strpos($line, 'A6:B6') !== false) $lines[$i] = str_replace("'A6:B6'", '"A" . ($rowOffset + 6) . ":B" . ($rowOffset + 6)', $lines[$i]);
        
        if (strpos($line, '{$colLet}5') !== false) $lines[$i] = str_replace('"{$colLet}5"', '"{$colLet}" . ($rowOffset + 5)', $lines[$i]);
        if (strpos($line, '{$colLet}6') !== false) $lines[$i] = str_replace('"{$colLet}6"', '"{$colLet}" . ($rowOffset + 6)', $lines[$i]);
        if (strpos($line, '{$colLet}7') !== false) $lines[$i] = str_replace('"{$colLet}7"', '"{$colLet}" . ($rowOffset + 7)', $lines[$i]);
        if (strpos($line, '{$colLet}8') !== false) $lines[$i] = str_replace('"{$colLet}8"', '"{$colLet}" . ($rowOffset + 8)', $lines[$i]);
        if (strpos($line, '{$colLet}9') !== false) $lines[$i] = str_replace('"{$colLet}9"', '"{$colLet}" . ($rowOffset + 9)', $lines[$i]);
        if (strpos($line, '{$colLet}10') !== false) $lines[$i] = str_replace('"{$colLet}10"', '"{$colLet}" . ($rowOffset + 10)', $lines[$i]);
        if (strpos($line, '{$colLet}11') !== false) $lines[$i] = str_replace('"{$colLet}11"', '"{$colLet}" . ($rowOffset + 11)', $lines[$i]);
        if (strpos($line, '{$colLet}12') !== false) $lines[$i] = str_replace('"{$colLet}12"', '"{$colLet}" . ($rowOffset + 12)', $lines[$i]);
        if (strpos($line, '{$colLet}13') !== false) $lines[$i] = str_replace('"{$colLet}13"', '"{$colLet}" . ($rowOffset + 13)', $lines[$i]);
        
        if (strpos($line, '{$colLet}{$rn}') !== false) $lines[$i] = preg_replace('/"\{\$colLet\}\{\$rn\}"/', '"{$colLet}" . ($rowOffset + $rn)', $lines[$i]);
        
        if (strpos($line, 'B{$rn}') !== false) $lines[$i] = preg_replace('/"B\{\$rn\}"/', '"B" . ($rowOffset + $rn)', $lines[$i]);
        
        if (strpos($line, 'A1:{$blockEnd}13') !== false) $lines[$i] = str_replace('"A1:{$blockEnd}13"', '"A" . ($rowOffset + 1) . ":{$blockEnd}" . ($rowOffset + 13)', $lines[$i]);
        
        if (strpos($line, 'C5') !== false) $lines[$i] = str_replace("'C5'", "'C' . (\$rowOffset + 5)", $lines[$i]);
        
        if (strpos($line, '$sheetIndex++;') !== false) {
            $lines[$i] = str_replace('$sheetIndex++;', "\$sheetIndex++;\n            \$rowOffset += 14;", $lines[$i]);
        }
    }
    
    // Stop processing after the loop ends
    if ($inExportExcel && strpos($line, '// Clean up the initial blank sheet') !== false) {
        $inLoop = false;
        $inExportExcel = false;
    }
}

file_put_contents($file, implode("\n", $lines));
echo "Successfully updated row offsets!\n";

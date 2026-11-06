<?php
namespace App\Services\Warehouse;
use Illuminate\Validation\ValidationException;
final class Quantity
{
    public static function units($value): int
    {
        if ($value === null || $value === '') $value = '0';
        if (!preg_match('/^(-?)(\d{1,10})(?:\.(\d{1,4}))?$/D', (string) $value, $m)) throw ValidationException::withMessages(['quantity' => 'Quantity must be a decimal with at most four places and ten integer digits.']);
        return ($m[1] === '-' ? -1 : 1) * ((int)$m[2] * 10000 + (int)str_pad($m[3] ?? '', 4, '0'));
    }
    public static function display($value): string
    {
        $value=(string)($value ?? '');
        return strpos($value,'.')===false ? $value : rtrim(rtrim($value,'0'),'.');
    }
    public static function text(int $units): string { return ($units < 0 ? '-' : '').intdiv(abs($units),10000).'.'.str_pad((string)(abs($units)%10000),4,'0',STR_PAD_LEFT); }
}

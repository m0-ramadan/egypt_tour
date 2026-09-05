<?php
namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::select('id', 'name', 'category_id', 'price', 'status', 'created_at')->get();
    }

    public function headings(): array
    {
        return ['ID', 'الاسم', 'التصنيف', 'السعر', 'الحالة', 'تاريخ الإضافة'];
    }
}
<?php

namespace Database\Seeders;

use App\Models\BookCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $book_categories = [
            [
                'name' => 'Technology',
                'description' => 'this book speas of technology'
            ],
            [
                'name' => 'Culture',
                'description' => 'this book speas of culture'
            ],
            [
                'name' => 'Religion',
                'description' => 'this book speas of religion'
            ],
        ];

        foreach ($book_categories as $category) {
            $category['created_by'] = 1;

            BookCategory::create($category);
        }
    }
}

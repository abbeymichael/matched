<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Accra', 'region' => 'Greater Accra', 'lat' => 5.6037, 'lng' => -0.1870],
            ['name' => 'Kumasi', 'region' => 'Ashanti', 'lat' => 6.6885, 'lng' => -1.6244],
            ['name' => 'Tamale', 'region' => 'Northern', 'lat' => 9.4008, 'lng' => -0.8393],
            ['name' => 'Takoradi', 'region' => 'Western', 'lat' => 4.8845, 'lng' => -1.7554],
            ['name' => 'Cape Coast', 'region' => 'Central', 'lat' => 5.1036, 'lng' => -1.2904],
            ['name' => 'Sunyani', 'region' => 'Bono', 'lat' => 7.3380, 'lng' => -2.3267],
            ['name' => 'Ho', 'region' => 'Volta', 'lat' => 6.6110, 'lng' => 0.4785],
            ['name' => 'Koforidua', 'region' => 'Eastern', 'lat' => 6.0941, 'lng' => -0.2570],
            ['name' => 'Wa', 'region' => 'Upper West', 'lat' => 10.0600, 'lng' => -2.5000],
            ['name' => 'Bolgatanga', 'region' => 'Upper East', 'lat' => 10.7856, 'lng' => -0.8514],
            ['name' => 'Tema', 'region' => 'Greater Accra', 'lat' => 5.6698, 'lng' => -0.0166],
            ['name' => 'Techiman', 'region' => 'Bono East', 'lat' => 7.5872, 'lng' => -1.9388],
            ['name' => 'Sekondi', 'region' => 'Western', 'lat' => 4.9340, 'lng' => -1.7130],
            ['name' => 'Obuasi', 'region' => 'Ashanti', 'lat' => 6.2000, 'lng' => -1.6833],
            ['name' => 'Dunkwa-on-Offin', 'region' => 'Central', 'lat' => 5.7790, 'lng' => -1.5530],
            ['name' => 'Nsawam', 'region' => 'Eastern', 'lat' => 5.8000, 'lng' => -0.3500],
            ['name' => 'Swedru', 'region' => 'Central', 'lat' => 5.5160, 'lng' => -0.7000],
            ['name' => 'Winneba', 'region' => 'Central', 'lat' => 5.3512, 'lng' => -0.6231],
            ['name' => 'Yendi', 'region' => 'Northern', 'lat' => 9.4333, 'lng' => -0.0167],
            ['name' => 'Navrongo', 'region' => 'Upper East', 'lat' => 10.8956, 'lng' => -1.0921],
            ['name' => 'Bawku', 'region' => 'Upper East', 'lat' => 11.0600, 'lng' => -0.2400],
            ['name' => 'Damongo', 'region' => 'Savannah', 'lat' => 9.0833, 'lng' => -1.8167],
            ['name' => 'Goaso', 'region' => 'Ahafo', 'lat' => 6.8000, 'lng' => -2.5167],
            ['name' => 'Mampong', 'region' => 'Ashanti', 'lat' => 7.0667, 'lng' => -1.4000],
            ['name' => 'Ejura', 'region' => 'Ashanti', 'lat' => 7.3833, 'lng' => -1.7167],
            ['name' => 'Konongo', 'region' => 'Ashanti', 'lat' => 6.6167, 'lng' => -1.2167],
            ['name' => 'Tarkwa', 'region' => 'Western', 'lat' => 5.3000, 'lng' => -2.0000],
            ['name' => 'Axim', 'region' => 'Western', 'lat' => 4.8667, 'lng' => -2.2333],
            ['name' => 'Sefwi Wiawso', 'region' => 'Western North', 'lat' => 6.2000, 'lng' => -2.4667],
            ['name' => 'Kpando', 'region' => 'Volta', 'lat' => 6.9833, 'lng' => 0.3000],
            ['name' => 'Hohoe', 'region' => 'Volta', 'lat' => 7.1500, 'lng' => 0.4667],
            ['name' => 'Akim Oda', 'region' => 'Eastern', 'lat' => 5.9167, 'lng' => -0.9833],
            ['name' => 'Bibiani', 'region' => 'Western North', 'lat' => 6.3167, 'lng' => -2.3333],
            ['name' => 'Berekum', 'region' => 'Bono', 'lat' => 7.7500, 'lng' => -2.5833],
            ['name' => 'Dormaa Ahenkro', 'region' => 'Bono', 'lat' => 7.2833, 'lng' => -2.8667],
            ['name' => 'Jirapa', 'region' => 'Upper West', 'lat' => 10.5333, 'lng' => -2.7167],
            ['name' => 'Nalerigu', 'region' => 'North East', 'lat' => 10.3333, 'lng' => -0.4000],
            ['name' => 'Salaga', 'region' => 'Savannah', 'lat' => 8.5500, 'lng' => -0.5167],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(
                ['name' => $city['name'], 'region' => $city['region']],
                array_merge($city, ['sort_order' => 0])
            );
        }
    }
}

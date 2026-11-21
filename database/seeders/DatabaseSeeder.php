<?php

namespace Database\Seeders;

use App\Enums\Month;
use App\Enums\PlantTrait;
use App\Models\Color;
use App\Models\Cultivar;
use App\Models\Family;
use App\Models\Foliage;
use App\Models\GardenStyle;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\PlantBlooming;
use App\Models\PlantBloomingMonth;
use App\Models\PlantEnvironment;
use App\Models\PlantMaintenance;
use App\Models\PlantProperties;
use App\Models\PlantPropertiesTrait;
use App\Models\PlantResilience;
use App\Models\SoilType;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Made for fresh migration's and might create too much records if used again
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
        ]);
        
        $this->call([
            ColorSeeder::class,
            GardenStyleSeeder::class,
            SoilTypeSeeder::class,
        ]);

        $this->call([
            FamilySeeder::class,
            GenusSeeder::class,
            SpeciesSeeder::class,
            PlantSeeder::class
        ]);

        /*Family::factory()
            ->count(5)
            ->has(Genus::factory()
                ->count(3)
                ->has(Species::factory()
                    ->count(2)
                    ->has(Plant::factory()
                        ->count(10)
                        ->has(Cultivar::factory()
                            ->count(2)
                            ->hasProperties()
                            ->hasFoliage()
                            ->hasEnvironment()
                            ->hasMaintenance()
                            ->hasBlooming()
                        )
                    )
                )
            )
            ->create();/** */


        
        // $allColors = Color::all();
        // $allSoils = SoilType::all();
        // $allGardens = GardenStyle::all();

        // Cultivar::all()->each(function (Cultivar $cultivar) use ($allColors): void {
        //     $cultivar->colors()->sync(
        //         ids: $allColors->random(number: rand(min: 1, max: 3))->pluck('id')->toArray()
        //     );

        //     if (mt_rand(0, 1)) {
        //         $cultivar->properties()->save(
        //             model: PlantProperties::factory()->make()
        //         );
        //     }

        //     if (mt_rand(0 ,1)) {
        //         $cultivar->foliage()->save(
        //             model: Foliage::factory()->make()
        //         );
        //     }

        //     // will always be made upon creating a cultivar due to the slider being required
        //     // it will still work when not created, but that shouldn't happen
        //     $cultivar->environment()->save(
        //         model: PlantEnvironment::factory()->make()
        //     );

        //     if (mt_rand(0, 1)) {
        //         $cultivar->maintenance()->save(
        //             model: PlantMaintenance::factory()->make()
        //         );
        //     }

        //     if (mt_rand(0, 1)) {
        //         $cultivar->blooming()->save(
        //             model: PlantBlooming::factory()->make()
        //         );
        //     }
        // });/* */

        // PlantProperties::all()->each(function (PlantProperties $properties) use ($allGardens): void {
        //     $properties->gardenStyles()->sync(
        //         ids: $allGardens->random(number: rand(min: 0, max: 3))->pluck('id')->toArray()
        //     );


        //     if (mt_rand(0, 1)) {
        //         $traits = collect(PlantTrait::cases())->random(number: rand(min: 1, max: 5))->all();

        //         foreach ($traits as $trait) {
        //             $properties->traits()->save(
        //                 PlantPropertiesTrait::factory()->make(['trait_index' => $trait])
        //             );
        //         }
        //     }
        // });/* */

        // Foliage::all()->each(function (Foliage $foliage) use ($allColors): void {
        //     $colorIds = $allColors->random(number: rand(min: 1, max: 3))->pluck('id');
        //     $syncData = $colorIds->mapWithkeys(fn ($id) => [
        //         $id => ['tag' => 'default']
        //     ])->toArray();

        //     $foliage->colors()->syncWithoutDetaching($syncData);

        //     if ($foliage->seasonal_color_change == 1) {
        //         $syncData = [];


        //         foreach (['spring', 'summer', 'fall', 'winter'] as $season) {
        //             $count = fake()->randomElement([0, 0, 0, 1, 2, 3]);

        //             if ($count > 0) {
        //                 $colorIds = $allColors->random(number: $count)->pluck('id');

        //                 foreach ($colorIds as $id) {
        //                     $syncData[$id] = ['tag' => $season];
        //                 }
        //             }
        //         }

        //         $foliage->colors()->syncWithoutDetaching(ids: $syncData);
        //     }
        // });/* */

        // PlantEnvironment::all()->each(function (PlantEnvironment $environment) use ($allSoils): void {
        //     $environment->soilTypes()->sync(
        //         ids: $allSoils->random(number: rand(min: 1, max: 3))->pluck('id')->toArray()
        //     );
        // });/* */


        // PlantPropertiesTrait::factory(count: 20)->create();

    }
}

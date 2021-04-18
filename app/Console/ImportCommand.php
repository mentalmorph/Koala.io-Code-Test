<?php

namespace App\Console;

use App\Models\Location;
use App\Services\Parsers\JsonMenuFeedParser;
use Database\Seeders\BrandsSeeder;
use Illuminate\Console\Command;
use App\Services\Contracts\LocationFeedParserInterface;
use App\Services\Parsers\JsonLocationFeedParser;
use App\Services\Parsers\FeedParser;
use App\Services\Parsers\XmlLocationFeedParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class ImportCommand extends Command
{
    protected $signature = 'koala:import';

    protected $description = 'Imports data provided for the Koala code test.';

    /**
     * @var LocationFeedParserInterface[]
     */
    protected array $locationFeedParsers;

    public function handle(): void
    {
        $restaurantConfigurations = Config::get('feeds.restaurants');

        //Run brand seeder
        app(BrandsSeeder::class)->run();

        foreach ($restaurantConfigurations as $restaurantConfigurationName => $restaurantConfiguration) {
            $this->info('Starting Restaurant "' . $restaurantConfigurationName . '" import...');
            foreach ($restaurantConfiguration as $phase => $filePath) {
                $ucPhase = ucfirst($phase);
                $this->info($ucPhase . ' - Importing from ' . $filePath);

                $parser = $this->parserBasedOnExtensionAndPhase($filePath, $phase);

                if (!$parser) {
                    $this->warn($phase . ' parser does not exist');
                    continue;
                }

                $objects = $parser->parse($filePath);

                $objects->each(
                    fn (Model $obj) =>
                        $obj->updateOrCreate(
                            ['feed_id' => $obj->feed_id],
                            $obj->getAttributes()
                        )
                );
            }
        }
    }

    protected function parserBasedOnExtensionAndPhase(string $path, string $phase): ?FeedParser
    {
        $pathParts = explode('.', $path);
        $extension = end($pathParts);

        return match ([$phase, $extension]) {
            ['locations', 'json'] => app(JsonLocationFeedParser::class),
            ['locations', 'xml'] => app(XmlLocationFeedParser::class),
            ['menus', 'json'] => app(JsonMenuFeedParser::class),
            default => null
        };
    }
}

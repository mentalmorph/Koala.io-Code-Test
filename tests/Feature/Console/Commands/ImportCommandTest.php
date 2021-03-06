<?php

namespace Console\Commands;

use App\Models\Location;
use App\Services\Parsers\JsonLocationFeedParser;
use App\Services\Parsers\JsonMenuFeedParser;
use App\Services\Parsers\XmlLocationFeedParser;
use App\Services\Parsers\XmlMenuFeedParser;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

class ImportCommandTest extends TestCase
{
    public function test_it_uses_the_json_parser_for_json_files()
    {
        Config::set('feeds.restaurants', []);
        Config::set('feeds.restaurants.Test Restaurant', [
            'locations' =>  base_path('tests/Fixtures/koala-json-eatery-location.json'),
            'menus' => base_path('tests/Fixtures/koala-json-eatery-menu.json')
        ]);

        $locationParserSpy = Mockery::spy(app(JsonLocationFeedParser::class))->makePartial();
        $menuParserSpy = Mockery::spy(app(JsonMenuFeedParser::class))->makePartial();

        $this->app->bind(
            JsonLocationFeedParser::class,
            fn() => $locationParserSpy
        );

        $this->app->bind(
            JsonMenuFeedParser::class,
            fn() => $menuParserSpy
        );

        $this->artisan('koala:import');

        $locationParserSpy->shouldHaveReceived('parse')->with(Config::get('feeds.restaurants.Test Restaurant.locations'))->once();
        $menuParserSpy->shouldHaveReceived('parse')->with(Config::get('feeds.restaurants.Test Restaurant.menus'))->once();
    }

    public function test_it_uses_the_xml_parser_for_xml_files()
    {
        Config::set('feeds.restaurants', []);
        Config::set('feeds.restaurants.Test Restaurant', [
            'locations' => base_path('tests/Fixtures/koala-xml-grill-data.xml')
        ]);

        $locationParserSpy = Mockery::spy(app(XmlLocationFeedParser::class))->makePartial();

        $this->app->bind(
            XmlLocationFeedParser::class,
            fn() => $locationParserSpy
        );

        $this->artisan('koala:import');

        $locationParserSpy->shouldHaveReceived('parse')
            ->once()
            ->with(Config::get('feeds.restaurants.Test Restaurant.locations'));
    }

    public function test_it_updates_locations_with_the_same_feed_id()
    {
        $expectedFeedId = 2; //Matches id in the fixture
        $oldLocation = Location::factory()->create(['feed_id' => $expectedFeedId]);

        Config::set('feeds.restaurants.Test Restaurant', [
            'locations' => base_path('tests/Fixtures/koala-json-eatery-location.json'),
        ]);

        $this->artisan('koala:import');

        $this->assertCount(1, Location::where('feed_id', $expectedFeedId)->get());
        $newLocation = Location::where('feed_id', $expectedFeedId)->first();
        $this->assertNotEquals($oldLocation->name, $newLocation->name);
        $this->assertNotEquals($oldLocation->phone_number, $newLocation->phone_number);
    }
}

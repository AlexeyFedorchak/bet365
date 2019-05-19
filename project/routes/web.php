<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/matches', function () {

            $matches = \App\Match::all();
            $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne(\Schema::getColumnListing('metches'));

            foreach ($matches as $match) {
                $csv->insertOne($match->toArray());
            }

           $csv->output('matches.csv');    
});


Route::get('/leagues', function () {

            $matches = \App\League::all();
            $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne(\Schema::getColumnListing('metches'));

            foreach ($matches as $match) {
                $csv->insertOne($match->toArray());
            }

           $csv->output('leagues.csv');    
});


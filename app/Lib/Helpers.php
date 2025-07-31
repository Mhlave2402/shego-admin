<?php

use App\CentralLogics\Helpers;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\BusinessManagement\Entities\ExternalConfiguration;
use Modules\BusinessManagement\Entities\ReferralEarningSetting;
use Modules\UserManagement\Entities\User;
use Pusher\Pusher;
use Pusher\PusherException;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\BusinessManagement\Entities\BusinessSetting;
use Modules\BusinessManagement\Entities\FirebasePushNotification;

if (!function_exists('translate')) {
    function translate(string $key, array $replace = [], ?string $locale = null): array|string|Translator|null
    {
        $locale = $locale ?? app()->getLocale();
        try {
            $langFilePath = base_path("resources/lang/{$locale}/lang.php");
            $translations = include $langFilePath;
            $normalizedKey = removeSpecialCharacters($key);
            $defaultValue = ucfirst(str_replace('_', ' ', $normalizedKey));
            $translatedValue = str_replace(['{', '}'], [':', ''], $defaultValue);

            if (!array_key_exists($normalizedKey, $translations)) {
                $translations[$normalizedKey] = $locale === 'en'
                    ? $translatedValue
                    : autoTranslator(q: $translatedValue, sl: 'en', tl: $locale);

                $exported = "<?php return " . var_export($translations, true) . ";";
                file_put_contents($langFilePath, $exported);
                $translation = $translations[$normalizedKey];
                foreach ($replace as $k => $v) {
                    $translation = str_replace(":$k", $v, $translation);
                }
                return $translation;
            }
            return trans("lang.{$normalizedKey}", $replace, $locale);
        } catch (\Exception $exception) {
            return trans("lang.{$normalizedKey}", $replace, $locale);
        }
    }
}
if (!function_exists('defaultLang')) {
    function defaultLang()
    {
        if (strpos(url()->current(), '/api')) {
            $lang = App::getLocale();
        } elseif (session()->has('locale')) {
            $lang = session('locale');
        } elseif (businessConfig('system_language', 'language_settings')) {
            $data = businessConfig('system_language', 'language_settings')->value;
            $code = 'en';
            $direction = 'ltr';
            foreach ($data as $ln) {
                if (array_key_exists('default', $ln) && $ln['default']) {
                    $code = $ln['code'];
                    if (array_key_exists('direction', $ln)) {
                        $direction = $ln['direction'];
                    }
                }
            }
            session()->put('locale', $code);
            session()->put('direction', $direction);
            $lang = $code;
        } else {
            $lang = App::getLocale();
        }
        return $lang;
    }
}


if (!function_exists('removeSpecialCharacters')) {
    function removeSpecialCharacters(string|null $text): string|null
    {
        return str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', preg_replace('/\s\s+/', ' ', $text));
    }
}

if (!function_exists('fileUploader')) {
    function fileUploader(string $dir, string $format, $image = null, $oldImage = null)
    {
        if ($image == null) {
            return $oldImage ?? 'def.png';
        }
        if (is_array($oldImage) && !empty($oldImage)) {
            // Handle the case when $oldImage is an array (multiple images)
            foreach ($oldImage as $file) {
                Storage::disk('public')->delete($dir . $file);
            }
        } elseif (is_string($oldImage) && !empty($oldImage)) {
            // Handle the case when $oldImage is a single image (string)
            Storage::disk('public')->delete($dir . $oldImage);
        }

        $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        Storage::disk('public')->put($dir . $imageName, file_get_contents($image));

        return $imageName;
    }
}

if (!function_exists('fileRemover')) {
    function fileRemover(string $dir, $image)
    {
        if (!isset($image)) return true;

        if (Storage::disk('public')->exists($dir . $image)) Storage::disk('public')->delete($dir . $image);

        return true;
    }
}

if (!function_exists('paginationLimit')) {
    function paginationLimit()
    {
        return getSession('pagination_limit') == false ? 10 : getSession('pagination_limit');
    }
}

if (!function_exists('stepValue')) {
    function stepValue()
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        return 1 / pow(10, $points);
    }
}
if (!function_exists('businessConfig')) {
    function businessConfig($key, $settingsType = null)
    {
        try {
            $config = BusinessSetting::query()
                ->where('key_name', $key)
                ->when($settingsType, function ($query) use ($settingsType) {
                    $query->where('settings_type', $settingsType);
                })
                ->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (!function_exists('newBusinessConfig')) {
    function newBusinessConfig($key, $settingsType = null)
    {
        $businessSettings = Cache::rememberForever(CACHE_BUSINESS_SETTINGS, function () {
            return BusinessSetting::all();
        });

        try {
            $config = $businessSettings->where('key_name', $key)
                ->when($settingsType, function ($query) use ($settingsType) {
                    $query->where('settings_type', $settingsType);
                })
                ->first()?->value;
        } catch (Exception $exception) {
            return null;
        }
        return (isset($config)) ? $config : null;
    }
}
if (!function_exists('referralEarningSetting')) {
    function referralEarningSetting($key, $settingsType = null)
    {
        try {
            $config = ReferralEarningSetting::query()
                ->where('key_name', $key)
                ->when($settingsType, function ($query) use ($settingsType) {
                    $query->where('settings_type', $settingsType);
                })
                ->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}
if (!function_exists('externalConfig')) {
    function externalConfig($key)
    {
        try {
            $config = ExternalConfiguration::query()
                ->where('key', $key)
                ->first();
        } catch (Exception $exception) {
            return null;
        }
        return (isset($config)) ? $config : null;
    }
}
if (!function_exists('checkExternalConfiguration')) {
    function checkExternalConfiguration($externalBaseUrl, $externalTokem, $drivemondToken)
    {
        $activationMode = externalConfig('activation_mode')?->value;
        $martBaseUrl = externalConfig('mart_base_url')?->value;
        $martToken = externalConfig('mart_token')?->value;
        $systemSelfToken = externalConfig('system_self_token')?->value;
        return $activationMode == 1 && $martBaseUrl == $externalBaseUrl && $martToken == $externalTokem && $systemSelfToken == $drivemondToken;
    }
}
if (!function_exists('checkSelfExternalConfiguration')) {
    function checkSelfExternalConfiguration()
    {
        $activationMode = externalConfig('activation_mode')?->value;
        $martBaseUrl = externalConfig('mart_base_url')?->value;
        $martToken = externalConfig('mart_token')?->value;
        $systemSelfToken = externalConfig('system_self_token')?->value;
        return $activationMode == 1 && $martBaseUrl != null && $martToken != null && $systemSelfToken != null;
    }
}

if (!function_exists('generateReferralCode')) {
    function generateReferralCode($user = null)
    {
        $refCode = strtoupper(Str::random(10));
        if (User::where('ref_code', $refCode)->exists()) {
            generateReferralCode();
        }
        if ($user) {
            $user->ref_code = $refCode;
            $user->save();
        }
        return $refCode;
    }
}


if (!function_exists('responseFormatter')) {
    function responseFormatter($constant, $content = null, $limit = null, $offset = null, $errors = []): array
    {
        $data = [
            'total_size' => isset($limit) ? $content->total() : null,
            'limit' => $limit,
            'offset' => $offset,
            'data' => $content,
            'errors' => $errors,
        ];
        $responseConst = [
            'response_code' => $constant['response_code'],
            'message' => translate($constant['message']),
        ];
        return array_merge($responseConst, $data);
    }
}

if (!function_exists('errorProcessor')) {
    function errorProcessor($validator)
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $errors[] = ['error_code' => $index, 'message' => translate($error[0])];
        }
        return $errors;
    }
}


function autoTranslator($q, $sl, $tl): string
{
    $placeholders = [];
    $i = 0;
    $q = str_replace(['{', '}'], [':', ''], $q);
    $q = preg_replace_callback('/(:\w+)/', function ($matches) use (&$placeholders, &$i) {
        $token = 'XXPLACEHOLDER' . $i . 'XX';
        $placeholders[$token] = $matches[1];
        $i++;
        return $token;
    }, $q);

    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=$sl&tl=$tl&dt=t&q=" . urlencode($q);
    $res = file_get_contents($url);
    $res = json_decode($res, true);
    $translated = $res[0][0][0] ?? '';

    foreach ($placeholders as $token => $original) {
        $translated = str_ireplace($token, $original, $translated);
    }

    return $translated;
}

if (!function_exists('getLanguageCode')) {
    function getLanguageCode(string $countryCode): string
    {
        foreach (LANGUAGES as $locale) {
            if ($countryCode == $locale['code']) {
                return $countryCode;
            }
        }
        return "en";
    }

}

if (!function_exists('exportData')) {
    function exportData($data, $file, $viewPath)
    {
        return match ($file) {
            'csv' => (new FastExcel($data))->download(time() . '-file.csv'),
            'excel' => (new FastExcel($data))->download(time() . '-file.xlsx'),
            'pdf' => Pdf::loadView($viewPath, ['data' => $data])->download(time() . '-file.pdf'),
            default => view($viewPath, ['data' => $data]),
        };
    }
}


if (!function_exists('logViewerNew')) {

    function logViewerNew($logs, $file = null)
    {
        if ($file) {
            $data = $logs->map(function ($item) {
                $objects = explode("\\", $item->logable_type);
                return [
                    'edited_date' => date('Y-m-d', strtotime($item->created_at)),
                    'edited_time' => date('h:i A', strtotime($item->created_at)),
                    'email' => $item->users?->email,
                    'edited_object' => end($objects),
                    'before' => json_encode($item?->before),
                    'after' => json_encode($item?->after)
                ];
            });
            return exportData($data, $file, 'adminmodule::log-print');
        }
        return view('adminmodule::activity-log', compact('logs'));
    }
}


if (!function_exists('get_cache')) {
    function get_cache($key)
    {
        if (!Cache::has($key)) {
            $config = businessConfig($key)?->value;
            if (!$config) {
                return null;
            }
            Cache::put($key, $config);
        }
        return Cache::get($key);
    }
}

if (!function_exists('getSession')) {
    function getSession($key)
    {
        if (!Session::has($key)) {
            $config = businessConfig($key)?->value;
            if (!$config) {
                return false;
            }
            Session::put($key, $config);
        }
        return Session::get($key);
    }
}

if (!function_exists('haversineDistance')) {
    function haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}

if (!function_exists('getDateRange')) {
    function getDateRange($request)
    {
        if (is_array($request)) {
            return [
                'start' => Carbon::parse($request['start'])->startOfDay(),
                'end' => Carbon::parse($request['end'])->endOfDay(),
            ];
        }

        return match ($request) {
            TODAY => [
                'start' => Carbon::parse(now())->startOfDay(),
                'end' => Carbon::parse(now())->endOfDay()
            ],
            PREVIOUS_DAY => [
                'start' => Carbon::yesterday()->startOfDay(),
                'end' => Carbon::yesterday()->endOfDay(),
            ],
            THIS_WEEK => [
                'start' => Carbon::parse(now())->startOfWeek(),
                'end' => Carbon::parse(now())->endOfWeek(),
            ],
            THIS_MONTH => [
                'start' => Carbon::parse(now())->startOfMonth(),
                'end' => Carbon::parse(now())->endOfMonth(),
            ],
            LAST_7_DAYS => [
                'start' => Carbon::today()->subDays(7)->startOfDay(),
                'end' => Carbon::parse(now())->endOfDay(),
            ],
            LAST_WEEK => [
                'start' => Carbon::now()->subWeek()->startOfWeek(),
                'end' => Carbon::now()->subWeek()->endOfWeek(),
            ],
            LAST_MONTH => [
                'start' => Carbon::now()->subMonth()->startOfMonth(),
                'end' => Carbon::now()->subMonth()->endOfMonth(),
            ],
            THIS_YEAR => [
                'start' => Carbon::now()->startOfYear(),
                'end' => Carbon::now()->endOfYear(),
            ],
            ALL_TIME => [
                'start' => Carbon::parse(BUSINESS_START_DATE),
                'end' => Carbon::now(),
            ]
        };
    }
}
if (!function_exists('getCustomDateRange')) {
    function getCustomDateRange($dateRange)
    {
        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate));
        return [
            'start' => Carbon::parse($startDate)->startOfDay(),
            'end' => Carbon::parse($endDate)->endOfDay(),
        ];


    }
}

if (!function_exists('configSettings')) {
    function configSettings($key, $settingsType)
    {
        try {
            $config = DB::table('settings')->where('key_name', $key)
                ->where('settings_type', $settingsType)->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (!function_exists('languageLoad')) {
    function languageLoad()
    {
        if (\session()->has(LANGUAGE_SETTINGS)) {
            $language = \session(LANGUAGE_SETTINGS);
        } else {
            $language = businessConfig(SYSTEM_LANGUAGE)?->value;
            \session()->put(LANGUAGE_SETTINGS, $language);
        }
        return $language;
    }

}

if (!function_exists('set_currency_symbol')) {
    function set_currency_symbol($amount)
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        $position = getSession('currency_symbol_position') ?? 'left';
        $symbol = getSession('currency_symbol') ?? '$';

        if ($position == 'left') {
            return $symbol . ' ' . number_format($amount, $points);
        }
        return number_format($amount, $points) . ' ' . $symbol;
    }
}

if (!function_exists('getCurrencyFormat')) {
    function getCurrencyFormat($amount)
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        $position = getSession('currency_symbol_position') ?? 'left';
        if (session::has('currency_symbol')) {
            $symbol = session()->get('currency_symbol');
        } else {
            $symbol = businessConfig('currency_symbol', 'business_information')->value ?? "$";
        }

        if ($position == 'left') {
            return $symbol . ' ' . number_format($amount, $points);
        } else {
            return number_format($amount, $points) . ' ' . $symbol;
        }
    }
}


if (!function_exists('getNotification')) {
    function getNotification($key)
    {
        $notification = FirebasePushNotification::query()->firstWhere('name', $key);
        return [
            'title' => $notification['name'] ?? ' ',
            'description' => $notification['value'] ?? ' ',
            'status' => (bool)$notification['status'] ?? 0,
            'action' => $notification['action'] ?? ' ',
        ];
    }
}

if (!function_exists('getMainDomain')) {
    function getMainDomain($url)
    {
        // Remove protocol from the URL
        $url = preg_replace('#^https?://#', '', $url);

        // Split the URL by slashes
        $parts = explode('/', $url);

        // Extract the domain part
        // Return the subdomain and domain
        return $parts[0];
    }
}
#old route
//if (!function_exists('getRoutes')) {
//    function getRoutes(array $originCoordinates, array $destinationCoordinates, array $intermediateCoordinates = [], array $drivingMode = ["DRIVE"])
//    {
//        $apiKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? '';
//        $encoded_polyline = null;
//        $responses = [];
//        $origin = implode(',', $originCoordinates);
//        $destination = implode(',', $destinationCoordinates);
//        // Convert waypoints to string format
//        $waypointsFormatted = [];
//        if ($intermediateCoordinates && !is_null($intermediateCoordinates[0][0])) {
//            foreach ($intermediateCoordinates as $wp) {
//                $waypointsFormatted[] = $wp[0] . ',' . $wp[1];
//            }
//        }
//        $waypointsString = implode('|', $waypointsFormatted);
//        $response = Http::get("https://maps.googleapis.com/maps/api/directions/json?origin=$origin&destination=$destination&departure_time=now&waypoints=$waypointsString&key=$apiKey");
//        if ($response->successful()) {
//            $result = $response->json();
//            $distance = 0;
//            $duration = 0;
//            $durationInTraffic = 0;
//
//            // Process the JSON response data here
//            foreach ($result['routes'] as $route) {
//                $encoded_polyline = $route['overview_polyline']['points'];
//                foreach ($route['legs'] as $leg) {
//                    $distance += $leg['distance']['value'];
//                    $duration += $leg['duration']['value'];
//                    $durationInTraffic += $leg['duration_in_traffic']['value'] ?? $leg['duration']['value']; // Fallback to regular duration if traffic data is missing
//                }
//            }
//
//            $distance = str_replace(',', '', $distance);
//            $convert_to_bike = 1.2;
//
//            $responses[0] = [
//                'distance' => (double)str_replace(',', '', number_format(($distance ?? 0) / 1000, 2)),
//                'distance_text' => number_format(($distance ?? 0) / 1000, 2) . ' ' . 'km',
//                'duration' => number_format((($duration / 60) / $convert_to_bike), 2) . ' ' . 'min',
//                'duration_sec' => (int)($duration / $convert_to_bike),
//                'duration_in_traffic' => number_format((($durationInTraffic / 60) / $convert_to_bike), 2) . ' ' . 'min',
//                'duration_in_traffic_sec' => (int)($durationInTraffic / $convert_to_bike),
//                'status' => "OK",
//                'drive_mode' => 'TWO_WHEELER',
//                'encoded_polyline' => $encoded_polyline,
//            ];
//
//            $responses[1] = [
//                'distance' => (double)str_replace(',', '', number_format(($distance ?? 0) / 1000, 2)),
//                'distance_text' => number_format(($distance ?? 0) / 1000, 2) . ' ' . 'km',
//                'duration' => number_format(($duration / 60), 2) . ' ' . 'min',
//                'duration_sec' => (int)$duration,
//                'duration_in_traffic' => number_format(($durationInTraffic / 60), 2) . ' ' . 'min',
//                'duration_in_traffic_sec' => (int)$durationInTraffic,
//                'status' => "OK",
//                'drive_mode' => 'DRIVE',
//                'encoded_polyline' => $encoded_polyline,
//            ];
//
//            return $responses;
//        } else {
//            // Handle the error if the request was not successful
//            return $response->status();
//        }
//
//    }
//}

if (!function_exists('getRoutes')) {
    function getRoutes(array $originCoordinates, array $destinationCoordinates, array $intermediateCoordinates = [], array $drivingMode = ["DRIVE"])
    {
        $mapApiKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? '';
        $url = "https://routes.googleapis.com/directions/v2:computeRoutes";

        $origin = [
            "location" => [
                "latLng" => [
                    "latitude" => $originCoordinates[0],
                    "longitude" => $originCoordinates[1]
                ]
            ]
        ];

        $destination = [
            "location" => [
                "latLng" => [
                    "latitude" => $destinationCoordinates[0],
                    "longitude" => $destinationCoordinates[1]
                ]
            ]
        ];

        // Format waypoints
        $waypoints = [];
        if (!empty($intermediateCoordinates) && !is_null($intermediateCoordinates[0][0])) {
            foreach ($intermediateCoordinates as $wp) {
                $waypoints[] = [
                    "location" => [
                        "latLng" => [
                            "latitude" => $wp[0],
                            "longitude" => $wp[1]
                        ]
                    ]
                ];
            }
        }

        $data = [
            "origin" => $origin,
            "destination" => $destination,
            "intermediates" => $waypoints,
            "travelMode" => $drivingMode[0], // DRIVE, TWO_WHEELER, etc.
            "routingPreference" => "TRAFFIC_AWARE", // Enables traffic-based duration
            "computeAlternativeRoutes" => false,
            "languageCode" => "en-US",
            "units" => "METRIC"
        ];


        // API Headers
        $headers = [
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => $mapApiKey,
            'X-Goog-FieldMask' => '*'
        ];

        // Send POST request
        $response = Http::withHeaders($headers)->post($url, $data);

        if (!isset($response['routes'][0])) {
            // Fallback to car route
            $data['travelMode'] = 'DRIVE';
            $response = Http::withHeaders($headers)->post($url, $data);
        }

        if ($response->successful()) {
            $result = $response->json();
            if (!isset($result['routes'][0])) {
                return ['error' => 'No route found'];
            }

            $route = $result['routes'][0];
            $encoded_polyline = $route['polyline']['encodedPolyline'] ?? null;
            $distance = $route['distanceMeters'] ?? 0;
            $duration = $route['duration'] ?? '0s';
            $durationInTraffic = $route['staticDuration'] ?? $duration; // Fallback to normal duration if no traffic data

            // Convert duration to seconds
            preg_match('/(\d+)s/i', $duration, $matches);
            $durationSec = isset($matches[1]) ? (int)$matches[1] : 0;

            // Convert traffic duration to seconds
            preg_match('/(\d+)s/i', $durationInTraffic, $trafficMatches);
            $durationInTrafficSec = isset($trafficMatches[1]) ? (int)$trafficMatches[1] : 0;

            $convert_to_bike = 1.2; // Adjustment factor for bike mode

            $responses[0] = [
                'distance' => (double)number_format(($distance / 1000), 2),
                'distance_text' => number_format(($distance / 1000), 2) . ' km',
                'duration' => number_format((($durationSec / 60) / $convert_to_bike), 2) . ' min',
                'duration_sec' => (int)($durationSec / $convert_to_bike),
                'duration_in_traffic' => number_format((($durationInTrafficSec / 60) / $convert_to_bike), 2) . ' min',
                'duration_in_traffic_sec' => (int)($durationInTrafficSec / $convert_to_bike),
                'status' => "OK",
                'drive_mode' => 'TWO_WHEELER',
                'encoded_polyline' => $encoded_polyline,
            ];

            $responses[1] = [
                'distance' => (double)number_format(($distance / 1000), 2),
                'distance_text' => number_format(($distance / 1000), 2) . ' km',
                'duration' => number_format(($durationSec / 60), 2) . ' min',
                'duration_sec' => (int)$durationSec,
                'duration_in_traffic' => number_format(($durationInTrafficSec / 60), 2) . ' min',
                'duration_in_traffic_sec' => (int)$durationInTrafficSec,
                'status' => "OK",
                'drive_mode' => 'DRIVE',
                'encoded_polyline' => $encoded_polyline,
            ];

            return $responses;
        } else {
            return ['error' => 'API request failed', 'status' => $response->status(), 'details' => $response];
        }
    }
}


if (!function_exists('onErrorImage')) {
    function onErrorImage($data, $src, $error_src, $path)
    {
        if (isset($data) && strlen($data) > 1 && Storage::disk('public')->exists($path . $data)) {
            return $src;
        }
        return $error_src;
    }
}

if (!function_exists('checkPusherConnection')) {
    function checkPusherConnection($event)
    {
        try {
            // Pusher configuration
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
//            if (!empty($event)) {
//                $event;
//            }


            return response()->json(['message' => 'Pusher connection established successfully']);
        } catch (PusherException $e) {

        } catch (\Exception $e) {
            // If cURL error 52 occurs
            if (strpos($e->getMessage(), 'cURL error 52') !== false) {
                return true;
            }
            return true;
        }
    }
}

if (!function_exists('checkReverbConnection')) {
    function checkReverbConnection()
    {
        $host = env('REVERB_HOST') ?? '127.0.0.1';
        $port = env('REVERB_PORT') ?? 6001;
        $timeout = 2;

        $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }

        return false;
    }
}
if (!function_exists('spellOutNumber')) {
    function spellOutNumber($number)
    {
        $number = strval($number);
        $digits = [
            "zero", "one", "two", "three", "four",
            "five", "six", "seven", "eight", "nine"
        ];
        $tens = [
            "", "", "twenty", "thirty", "forty",
            "fifty", "sixty", "seventy", "eighty", "ninety"
        ];
        $teens = [
            "ten", "eleven", "twelve", "thirteen", "fourteen",
            "fifteen", "sixteen", "seventeen", "eighteen", "nineteen"
        ];

        $result = '';

        if (strlen($number) > 15) {
            $quadrillions = substr($number, 0, -15);
            $number = substr($number, -15);
            $result .= spellOutNumber($quadrillions) . ' quadrillion ';
        }

        if (strlen($number) > 12) {
            $trillions = substr($number, 0, -12);
            $number = substr($number, -12);
            $result .= spellOutNumber($trillions) . ' trillion ';
        }

        if (strlen($number) > 9) {
            $billions = substr($number, 0, -9);
            $number = substr($number, -9);
            $result .= spellOutNumber($billions) . ' billion ';
        }

        if (strlen($number) > 6) {
            $millions = substr($number, 0, -6);
            $number = substr($number, -6);
            $result .= spellOutNumber($millions) . ' million ';
        }

        if (strlen($number) > 3) {
            $thousands = substr($number, 0, -3);
            $number = substr($number, -3);
            $result .= spellOutNumber($thousands) . ' thousand ';
        }

        if (strlen($number) > 2) {
            $hundreds = substr($number, 0, -2);
            $number = substr($number, -2);
            $result .= $digits[intval($hundreds)] . ' hundred ';
        }

        if ($number > 0) {
            if ($number < 10) {
                $result .= $digits[intval($number)];
            } elseif ($number < 20) {
                $result .= $teens[$number - 10];
            } else {
                $result .= $tens[$number[0]];
                if ($number[1] > 0) {
                    $result .= '-' . $digits[intval($number[1])];
                }
            }
        }

        return trim($result);
    }
}
if (!function_exists('abbreviateNumber')) {
    function abbreviateNumber($number)
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        $abbreviations = ['', 'K', 'M', 'B', 'T'];
        $abbreviated_number = $number;
        $abbreviation_index = 0;

        while ($abbreviated_number >= 1000 && $abbreviation_index < count($abbreviations) - 1) {
            $abbreviated_number /= 1000;
            $abbreviation_index++;
        }

        return round($abbreviated_number, $points) . $abbreviations[$abbreviation_index];
    }
}

if (!function_exists('abbreviateNumberWithSymbol')) {
    #TODO
    function abbreviateNumberWithSymbol($number)
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        $position = getSession('currency_symbol_position') ?? 'left';
        if (session::has('currency_symbol')) {
            $symbol = session()->get('currency_symbol');
        } else {
            $symbol = businessConfig('currency_symbol', 'business_information')->value ?? "$";
        }
        $abbreviations = ['', 'K', 'M', 'B', 'T'];
        $abbreviated_number = $number;
        $abbreviation_index = 0;

        while ($abbreviated_number >= 1000 && $abbreviation_index < count($abbreviations) - 1) {
            $abbreviated_number /= 1000;
            $abbreviation_index++;
        }

        if ($position == 'left') {
            return $symbol . ' ' . round($abbreviated_number, $points) . $abbreviations[$abbreviation_index];
        } else {
            return round($abbreviated_number, $points) . $abbreviations[$abbreviation_index] . ' ' . $symbol;
        }

    }
}
if (!function_exists('removeInvalidCharcaters')) {
    function removeInvalidCharcaters($str)
    {
        return str_ireplace(['\'', '"', ';', '<', '>'], ' ', $str);
    }
}

if (!function_exists('textVariableDataFormat')) {
    function textVariableDataFormat($value, $tipsAmount = null, $levelName = null, $walletAmount = null, $tripId = null,
                                    $userName = null, $withdrawNote = null, $paidAmount = null, $methodName = null,
                                    $referralRewardAmount = null, $otp = null, $parcelId = null, $approximateAmount = null,
                                    $sentTime = null, $vehicleCategory = null, $reason = null, $dropOffLocation = null,
                                    $customerName = null, $driverName = null, $pickUpLocation = null, $locale = null): array|string|Translator|null
    {
        $replace = compact(
            'tipsAmount', 'levelName', 'walletAmount', 'tripId', 'userName', 'withdrawNote',
            'paidAmount', 'methodName', 'referralRewardAmount', 'otp', 'parcelId', 'approximateAmount',
            'sentTime', 'vehicleCategory', 'reason', 'dropOffLocation', 'customerName', 'driverName', 'pickUpLocation'
        );
        return translate(key: $value, replace: array_filter($replace, fn($value) => $value !== null), locale: $locale);
    }
}
if (!function_exists('smsTemplateDataFormat')) {
    function smsTemplateDataFormat($value, $customerName = null, $parcelId = null, $trackingLink = null)
    {
        $data = $value;
        if ($value) {
            if ($customerName) {
                $data = str_replace("{CustomerName}", $customerName, $data);
            }
            if ($parcelId) {
                $data = str_replace("{ParcelId}", $parcelId, $data);
            }
            if ($trackingLink) {
                $data = str_replace("{TrackingLink}", $trackingLink, $data);
            }
        }

        return $data;
    }
}
if (!function_exists('checkMaintenanceMode')) {
    function checkMaintenanceMode(): array
    {
        $maintenanceSystemArray = ['user_app', 'driver_app'];
        $selectedMaintenanceSystem = businessConfig('maintenance_system_setup')?->value ?? [];

        $maintenanceSystem = [];
        foreach ($maintenanceSystemArray as $system) {
            $maintenanceSystem[$system] = in_array($system, $selectedMaintenanceSystem) ? 1 : 0;
        }

        $selectedMaintenanceDuration = businessConfig('maintenance_duration_setup')?->value ?? [];
        $maintenanceStatus = (integer)(businessConfig('maintenance_mode')?->value ?? 0);

        $status = 0;
        if ($maintenanceStatus == 1) {
            if (isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'until_change') {
                $status = $maintenanceStatus;
            } else {
                if (isset($selectedMaintenanceDuration['start_date']) && isset($selectedMaintenanceDuration['end_date'])) {
                    $start = Carbon::parse($selectedMaintenanceDuration['start_date']);
                    $end = Carbon::parse($selectedMaintenanceDuration['end_date']);
                    $today = Carbon::now();
                    if ($today->between($start, $end)) {
                        $status = 1;
                    }
                }
            }
        }

        return [
            'maintenance_status' => $status,
            'selected_maintenance_system' => count($maintenanceSystem) > 0 ? $maintenanceSystem : null,
            'maintenance_messages' => businessConfig('maintenance_message_setup')?->value ?? null,
            'maintenance_type_and_duration' => count($selectedMaintenanceDuration) > 0 ? $selectedMaintenanceDuration : null,
        ];
    }
}

if (!function_exists('insertBusinessSetting')) {
    function insertBusinessSetting($keyName, $settingType = null, $value = null)
    {
        $data = BusinessSetting::where('key_name', $keyName)->where('settings_type', $settingType)->first();
        if (!$data) {
            BusinessSetting::updateOrCreate(['key_name' => $keyName, 'settings_type' => $settingType], [
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return true;
    }
}

if (!function_exists('hexToRgb')) {
    function hexToRgb($hex)
    {
        // Remove the hash at the start if it's there
        $hex = ltrim($hex, '#');

        // If the hex code is in shorthand (3 characters), convert to full form
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        // Convert hex to RGB values
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "$r, $g, $b";
    }
}

if (!function_exists('formatCustomDate')) {
    function formatCustomDate($date)
    {
        $carbonDate = Carbon::parse($date);
        $now = Carbon::now();

        if ($carbonDate->isToday()) {
            return $carbonDate->format('g:i A'); // e.g., 3:53 PM
        } elseif ($carbonDate->isYesterday()) {
            return 'Yesterday';
        } elseif ($carbonDate->diffInDays($now) <= 5) {
            // Returns "X days ago" for dates within the last 5 days
            return $carbonDate->diffInDays($now) . ' days ago';
        } else {
            return $carbonDate->format('d M Y'); // e.g., 17 Nov 2024
        }
    }
}


if (!function_exists('formatCustomDateForTooltip')) {
    function formatCustomDateForTooltip($dateTime)
    {
        $timestamp = strtotime($dateTime);
        $now = time();

        if (date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
            return date('h:i A', $timestamp); // Format as 01:43 PM
        }

        $oneWeekAgo = strtotime('-1 week', $now);
        if ($timestamp > $oneWeekAgo) {
            return date('l h:i A', $timestamp);
        }

        return date('d M Y', $timestamp);
    }
}

if (!function_exists('getExtensionIcon')) {
    function getExtensionIcon($document)
    {
        $extension = pathinfo($document, PATHINFO_EXTENSION);
        $asset = asset('public/assets/admin-module/img/file-format/svg');
        return match ($extension) {
            'pdf' => $asset . '/pdf.svg',
            'cvc' => $asset . '/cvc.svg',
            'csv' => $asset . '/csv.svg',
            'doc', 'docx' => $asset . '/doc.svg',
            'jpg' => $asset . '/jpg.svg',
            'jpeg' => $asset . '/jpeg.svg',
            'webp' => $asset . '/webp.svg',
            'png' => $asset . '/png.svg',
            'xls' => $asset . '/xls.svg',
            'xlsx' => $asset . '/xlsx.svg',
            default => asset('public/assets/admin-module/img/document-upload.png'),
        };
    }
}

if (!function_exists('convertTimeToSecond')) {
    function convertTimeToSecond($time, $type)
    {
        $time = floatval($time);

        return match (strtolower($type)) {
            'second' => $time,
            'minute' => $time * 60,
            'hour' => $time * 3600,
            'day' => $time * 86400,
            default => null,
        };
    }
}

if (!function_exists('convertToSnakeCaseIfNeeded')) {
    function convertToSnakeCaseIfNeeded($string)
    {
        if (strpos($string, '-') !== false) {
            return str_replace('-', '_', $string);
        }
        return $string;
    }
}

if (!function_exists('pushSentTime')){
    function pushSentTime($time)
    {
        return Carbon::parse($time)->format('d M Y - h:i A');
    }
}


if (!function_exists('distanceCalculator')) {
    function distanceCalculator($data, $earthRadius = 6371)
    {
        $fromLongitude = (float)$data['from_longitude'];
        $fromLatitude = (float)$data['from_latitude'];
        $toLongitude = (float)$data['to_longitude'];
        $toLatitude = (float)$data['to_latitude'];
        $latDifference = deg2rad($toLatitude - $fromLatitude);
        $lonDifference = deg2rad($toLongitude - $fromLongitude);

        $a = sin($latDifference / 2) * sin($latDifference / 2) +
            cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) *
            sin($lonDifference / 2) * sin($lonDifference / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}

if (!function_exists('enableCronJobs')) {
    function enableCronJobs(array $commands)
    {
        try {
            $projectRoot = base_path();
            $existingCronJobs = trim(shell_exec('crontab -l 2>/dev/null'));

            $newCrons = [];
            foreach ($commands as $command) {
                $cron = "* * * * * cd $projectRoot && php artisan $command >> /dev/null 2>&1";
                if (strpos($existingCronJobs, "php artisan $command") === false) {
                    $newCrons[] = $cron;
                } else {
                    info("Cron for '$command' already exists");
                }
            }

            if (!empty($newCrons)) {
                $cronFile = tempnam(sys_get_temp_dir(), 'cron');
                $newCronJobs = $existingCronJobs
                    ? rtrim($existingCronJobs) . PHP_EOL . implode(PHP_EOL, $newCrons) . PHP_EOL
                    : implode(PHP_EOL, $newCrons) . PHP_EOL;

                file_put_contents($cronFile, $newCronJobs);
                $output = [];
                $returnVar = 0;
                exec("crontab $cronFile 2>&1", $output, $returnVar);
                if ($returnVar !== 0 || !empty($output)) {
                    info("crontab exec output: " . implode("\n", $output));
                    info("crontab exec return code: $returnVar");
                }
                unlink($cronFile);

                if ($returnVar === 0) {
                    info("Cron jobs added successfully");
                } else {
                    info("Failed to update cron jobs, return code: $returnVar");
                }
            }
        } catch (Throwable $e) {
            info("Failed to enable cron jobs, error: " . $e->getMessage());
        }
    }
}



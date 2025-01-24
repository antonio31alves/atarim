<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Url;

class UrlShorteningController extends Controller
{
    // Encode an original URL to a shortened URL
    public function encode(Request $request)
    {
        $request->validate([
            'original_url' => 'required|url',
        ]);

        $originalUrl = $request->input('original_url');

        // Check if the URL already exists
        $url = Url::where('original_url', $originalUrl)->first();
        if (!$url) {

            // Generate a unique short code with letters and numbers
            do {
                $shortUrlCode = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
            }while(Url::where('short_url_code', $shortUrlCode)->exists());

            // Save to the database
            $url = Url::create([
                'original_url' => $originalUrl,
                'short_url_code' => $shortUrlCode,
            ], 201); // Return 201 Created status
        }else {
            // Return an error if the URL already exists
            return response()->json([
                'error' => 'The URL already exists in the database.',
                'short_url' => env('SHORT_URL_DOMAIN') . '/' . $url->short_url_code,
            ], 200);
        }

        return response()->json([
            'original_url' => $url->original_url,
            'short_url_code' => env('SHORT_URL_DOMAIN') . '/' . $url->short_url_code,
        ]);
    }

    // Decode a short URL back to the original URL
    public function decode($shortUrlCode)
    {
        $url = Url::where('short_url_code', $shortUrlCode)->first();

        if (!$url) {
            return response()->json(['error' => 'Short URL not found.'], 404); // Return 404 not found status
        }

        return response()->json([
            'original_url' => $url->original_url,
        ]);
    }
}

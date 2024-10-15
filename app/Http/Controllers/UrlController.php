<?php

namespace App\Http\Controllers;

use App\Http\Clases\App;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\Cast\String_;


use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Url
 */
class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * This method handles a GET request to retrieve a list of URLs from the database. It allows filtering results based on optional search parameters, ordering results ascending or descending, and paginating the results.
     *
     * Accepted query parameters:
     * 
     * @queryParam search string Optional. A search filter based on partial URL matches. Example: "example.com".
     * @queryParam order_desc boolean Optional. Sorts the results in descending order by the `created_at` column. Default is true if not specified. Example: true.
     * @queryParam order_asc boolean Optional. Sorts the results in ascending order by the `created_at` column. If present, `order_desc` is not applied. Example: true.
     * @queryParam unpaginated boolean Optional. If present and true, it returns all results without pagination. Example: false.
     * @queryParam page integer Optional. The page number for pagination. Defaults to the first page if not specified. Example: 1.
     * 
     * @return \Illuminate\Http\JsonResponse
     *
     * The returned response has the following structure:
     * 
     * - `data`: A collection of items, each containing data retrieved by the `getData()` method of the `Url` model.
     * - `current_page`: The current page number.
     * - `last_page`: The total number of pages.
     * - `total`: The total number of items found.
     *
     * @response 200 {
     *    "data": [
     *        {
     *            "id": 3,
     *            "url": "https://www.example.com/",
     *            "url_key": "g9fA6ac8",
     *            "short_url": "http://127.0.0.1:8000/g9fA6ac8"
     *        },
     *    ],
     *    "current_page": 1,
     *    "last_page": 1,
     *    "total": 2
     *}
     * 
     * Additional details:
     * 
     * - If the `unpaginated` parameter is true, all records will be returned without pagination.
     * - If both `order_asc` and `order_desc` are specified, only the last evaluated parameter will be applied.
     * 
     * @throws \Illuminate\Validation\ValidationException If any query parameter is invalid.
     */

    public function index()
    {
        //Query parameters
        $validates = [
            //Exmple:
            'search' => 'sometimes|string',
            //Example: true
            'order_desc' => 'sometimes|boolean',
            //Example: true
            'order_asc' => 'sometimes|boolean',
            //Example: false
            'unpaginated' => 'sometimes|boolean',
            //Example: 1
            'page' => 'sometimes|integer',
        ];

        $search = request()->query('search');
        $orderDesc = request()->has('order_desc') ? request()->query('order_desc') : true;
        $orderAsc = request()->has('order_asc') ? request()->has('order_asc') : false;
        $unpaginated = request()->query('unpaginated');
        $page = request()->has('page') ? request()->query('page') : 1;

        $dBlist = Url::when($search, function ($query, $search) {
            $query->where('url', 'LIKE', "%$search%");
        })
            ->when($orderDesc, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->when($orderAsc, function ($query) {
                $query->orderBy('created_at', 'asc');
            });

        if ($unpaginated) {
            $dBList = $dBlist->get();
        } else {
            $dBList = $dBlist->paginate(App::QUANTITY_ITEMS_RETURNED_PER_PAGE, ['*'], 'page', $page);
        }

        $listaDevolver = collect();
        if ($dBList) {
            foreach ($dBList as $item) {
                $listaDevolver->push($item->getData());
            }
        }

        //Datos de paginado
        $pagTotalItems = $unpaginated == true  ? count($dBList) : $dBList->total();
        $pagTotal = $unpaginated == true  ? 1 : ceil($pagTotalItems / App::QUANTITY_ITEMS_RETURNED_PER_PAGE);
        $pagActual = $page;

        return response()->json([
            'data' => $listaDevolver,
            'current_page' => (int) $pagActual,
            'last_page' => $pagTotal,
            'total' => $pagTotalItems
        ], 200);
    }

    /**
     * Create a Shortened URL
     * 
     * This method allows a user to submit a full URL and generates a shortened version of it.
     * The provided URL is validated, ensuring it is a well-formed URL. A unique, random key is generated,
     * and the original URL and its shortened key are stored in the database. If the provided URL is not valid,
     * a validation error is returned.
     * 
     * @bodyParam url string required The original URL that needs to be shortened. Example: https://www.google.com
     * 
     * @response 201 {
     *    "data": {
     *        "id": 4,
     *        "url": "https://www.example.com",
     *        "url_key": "Lm2qnRc1",
     *        "short_url": "http://127.0.0.1:8000/Lm2qnRc1"
     *    }
     *}
     * 
     * @response 400 {
     *    "status": false,
     *    "errors": [
     *       "The url format is invalid."
     *    ]
     * }
     * 
     * @param \Illuminate\Http\Request $request The HTTP request containing the URL to shorten.
     * 
     * @return \Illuminate\Http\JsonResponse Returns the shortened URL on success or validation errors on failure.
     */

    public function store(Request $request)
    {

        //Body parameters
        $validates = [
            //Example: www.google.com
            'url' => 'required|url',

        ];

        $validator = Validator::make($request->input(), $validates);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        $randomKey = Str::random(App::CHARACTER_COUNT_URL_KEY);

        while (Url::where('url_key', $randomKey)->exists()) {
            $randomKey = Str::random(App::CHARACTER_COUNT_URL_KEY);
        }

        $url = Url::create([
            'url' => request()->input('url'),
            'url_key' => $randomKey,
        ]);

        return response()->json([
            'data' => $url->getData(),
            'message' => 'URL created successfully'
        ], 201);
    }

    /**
     * Retrieve the Original URL by its Shortened Key (url_key)
     * 
     * This method retrieves the original URL associated with a shortened key (`url_key`).
     * It searches the database for the provided key, and if the key exists, returns the original URL.
     * If the key is not found, an error message is returned indicating the URL was not found.
     * 
     * @urlParam url_key string required The shortened key of the URL to retrieve. Example: abc12345
     * 
     * @response 200 {
     *    "data": {
     *       "id": 1,
     *       "url": "https://example.com",
     *       "url_key": "abc12345",
     *       "created_at": "2024-01-01T12:00:00Z",
     *       "updated_at": "2024-01-01T12:00:00Z"
     *    }
     * }
     * 
     * @response 404 {
     *    "status": false,
     *    "message": "URL not found"
     * }
     * 
     * @param string $url_key The shortened URL key.
     * 
     * @return \Illuminate\Http\JsonResponse Returns the original URL if the key exists, or an error message if it doesn't.
     */
    public function show(string $url_key)
    {
        $url = Cache::remember("url_{$url_key}", 60, function () use ($url_key) {
            return Url::where('url_key', $url_key)->first();
        });
        
        if ($url->exists()) {
            return response()->json([
                "data" => $url->get()->first()->getData(),
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'URL not found'
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * This method handles a PUT or PATCH request to update an existing URL in the database.
     *
     * @param \Illuminate\Http\Request $request The incoming request containing the URL data to be updated.
     * @param Url $url The instance of the URL model that is being updated.
     * 
     * Accepted body parameters:
     * 
     * @bodyParam url string Optional. The new URL to update. It must be a valid URL format. Example: "https://www.google.com".
     * 
     * @return \Illuminate\Http\JsonResponse
     *
     * The returned response has the following structure:
     * 
     * - `data`: The updated URL object, containing the data retrieved by the `getData()` method of the `Url` model.
     * - `message`: A confirmation message indicating the success of the operation.
     *
     * @response 200 {
     *    "data": {
     *        "id": 4,
     *        "url": "https://www.example.com",
     *        "url_key": "Lm2qnRc1",
     *        "short_url": "http://127.0.0.1:8000/Lm2qnRc1"
     *    }
     *}
     * 
     * Example JSON response on validation failure:
     * 
     * ```json
     * {
     *   "status": false,
     *   "errors": [
     *     "The url field must be a valid URL."
     *   ]
     * }
     * ```
     *
     * Additional details:
     * 
     * - If the `url` parameter is not provided, the existing URL in the database remains unchanged.
     * - This method validates the incoming request data and returns a 400 response if validation fails.
     * 
     * @throws \Illuminate\Validation\ValidationException If the provided URL is invalid.
     */

    public function update(Request $request, Url $url)
    {
        //Body parameters
        $validates = [
            //Example: www.google.com
            'url' => 'sometimes|url',
        ];

        $validator = Validator::make($request->input(), $validates);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        $url->update([
            'url' => request()->has('url') ? request()->input('url') : $url->url,
        ]);

        return response()->json([
            'data' => $url->getData(),
            'message' => 'URL created successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * This method handles a DELETE request to remove a URL from the database.
     *
     * @param Url $url The instance of the URL model that is to be deleted.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * The returned response contains the data of the deleted URL, structured as follows:
     *
     * - `data`: The data of the deleted URL object, retrieved using the `getData()` method of the `Url` model.
     *
     * @response 200 {
     *    "data": {
     *        "id": 4,
     *        "url": "https://www.example.com",
     *        "url_key": "Lm2qnRc1",
     *        "short_url": "http://127.0.0.1:8000/Lm2qnRc1"
     *    }
     *}
     *
     * Additional details:
     *
     * - This method assumes the URL exists in the database. If the URL does not exist, it will return a 404 error by default.
     * - The response will have a status code of 200 (HTTP_OK) upon successful deletion.
     *
     * @throws \Exception If there is an error during the deletion process.
     */

    public function destroy(Url $url)
    {
        $url->delete();
        return response()->json($url->getData(), Response::HTTP_OK);
    }
}

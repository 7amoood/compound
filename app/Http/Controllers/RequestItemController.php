<?php
namespace App\Http\Controllers;

use App\Models\RequestItem;
use Illuminate\Http\JsonResponse;

class RequestItemController extends Controller
{
    public function toggle(RequestItem $item): JsonResponse
    {
        $item->is_picked = ! $item->is_picked;
        $item->save();

        return response()->json([
            'success'   => true,
            'is_picked' => $item->is_picked,
        ]);
    }
}

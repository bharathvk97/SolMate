<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\{Favourite, Hostel, Mess};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavouriteController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['type'=>'required|in:hostel,mess','id'=>'required|integer']);
        $type  = $request->type === 'hostel' ? Hostel::class : Mess::class;
        $model = $type::findOrFail($request->id);
        $user  = $request->user();

        $fav = Favourite::where('user_id',$user->id)->where('favourable_type',$type)->where('favourable_id',$request->id)->first();
        if ($fav) {
            $fav->delete();
            return response()->json(['status'=>true,'saved'=>false,'message'=>'Removed from favourites.']);
        }
        Favourite::create(['user_id'=>$user->id,'favourable_type'=>$type,'favourable_id'=>$request->id]);
        return response()->json(['status'=>true,'saved'=>true,'message'=>'Saved to favourites.']);
    }

    public function index(Request $request): JsonResponse
    {
        $favs = Favourite::where('user_id',$request->user()->id)->with('favourable')->latest()->paginate(20);
        return response()->json(['status'=>true,'data'=>$favs]);
    }
}

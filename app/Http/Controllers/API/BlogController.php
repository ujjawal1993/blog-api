<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog; 
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
  
    public function store(Request $request){
        $request->validate([
            'title' => 'required|string|unique:blogs,title',
            'description'=>'required|string',
            'image'=>'nullable|image|max:2048',
        ]);

        $imagePath = $request->hasFile('image') 
            ? $request->file('image')->store('blogs','public') 
            : null;

        $blog = Blog::create([
            'user_id'=>Auth::id(),
            'title'=>$request->title,
            'description'=>$request->description,
            'image'=>$imagePath,
        ]);

        return response()->json([
            'status'=>'success',
            'blog'=>$blog
        ],201);
    }
    public function index(Request $request){
        $query = Blog::withCount('likes')->with('likes');

        if($request->filter=='most_liked'){
            $query->orderByDesc('likes_count');
        } else {
            $query->latest();
        }

        if($request->search){
            $query->where('title','like','%'.$request->search.'%')
                  ->orWhere('description','like','%'.$request->search.'%');
        }

        $blogs = $query->paginate(10);

        $blogs->getCollection()->transform(function($blog){
            $blog->liked_by_user = $blog->likes->contains('user_id', Auth::id());
            return $blog;
        });

        return response()->json([
            'status'=>'success',
            'blogs'=>$blogs
        ]);
    }

public function update(Request $request, $id){
    $blog = Blog::find($id);

    if (!$blog) {
        return response()->json([
            'status' => 'error',
            'message' => 'No record found'
        ], 404);
    }

    $request->validate([
        'title' => 'required|string|unique:blogs,title,' . $blog->id,
        'description' => 'required|string',
        'image' => 'nullable|image|max:2048',
    ]);

    $data = $request->only('title','description');

    if ($request->hasFile('image')) {
        if ($blog->image && \Storage::disk('public')->exists($blog->image)) {
            \Storage::disk('public')->delete($blog->image);
        }
        $data['image'] = $request->file('image')->store('blogs','public');
    }

    $blog->update($data);

    return response()->json([
        'status' => 'success',
        'blog' => $blog
    ]);
}
public function destroy($id){
    $blog = Blog::find($id);

    if (!$blog) {
        return response()->json([
            'status' => 'error',
            'message' => 'No record found'
        ], 404);
    }

    if ($blog->image && \Storage::disk('public')->exists($blog->image)) {
        \Storage::disk('public')->delete($blog->image);
    }

    $blog->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Blog deleted'
    ]);
}

public function toggleLike($id)
{
    $blog = Blog::find($id);

    if (!$blog) {
        return response()->json([
            'status' => 'error',
            'message' => 'No record found'
        ], 404);
    }

    $like = $blog->likes()->where('user_id', Auth::id())->first();

    if ($like) {
        $like->delete();
        $status = 'unliked';
    } else {
        $blog->likes()->create(['user_id' => Auth::id()]);
        $status = 'liked';
    }

    return response()->json([
        'status' => $status,
        'blog_id' => $blog->id,
        'likes_count' => $blog->likes()->count()
    ], 200);
}


}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSearchController extends Controller
{
    public function search(Request $request)
    {
        $this->authorize('view_admin_dashboard');

        $q = $request->input('q');
        $type = $request->input('type');

        $results = [];

        if (! $q) {
            return response()->json(['results' => $results]);
        }

        $limit = 10;

        if (! $type || $type === 'users') {
            $users = User::where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->limit($limit)->get()
                ->map(fn ($u) => ['type' => 'user', 'id' => $u->id, 'title' => $u->name, 'subtitle' => $u->email]);
            $results = array_merge($results, $users->toArray());
        }

        if (! $type || $type === 'posts') {
            $posts = Post::where('body', 'like', "%{$q}%")->limit($limit)->get()
                ->map(fn ($p) => ['type' => 'post', 'id' => $p->id, 'title' => 'Post #'.$p->id, 'subtitle' => Str::limit($p->body, 120)]);
            $results = array_merge($results, $posts->toArray());
        }

        if (! $type || $type === 'reports') {
            $reports = Report::where('description', 'like', "%{$q}%")->limit($limit)->get()
                ->map(fn ($r) => ['type' => 'report', 'id' => $r->id, 'title' => 'Report #'.$r->id, 'subtitle' => $r->status]);
            $results = array_merge($results, $reports->toArray());
        }

        if (! $type || $type === 'communities') {
            $communities = Community::where('name', 'like', "%{$q}%")->limit($limit)->get()
                ->map(fn ($c) => ['type' => 'community', 'id' => $c->id, 'title' => $c->name, 'subtitle' => $c->status]);
            $results = array_merge($results, $communities->toArray());
        }

        if (! $type || $type === 'verifications') {
            $vrs = VerificationRequest::where('submitted_name', 'like', "%{$q}%")->limit($limit)->get()
                ->map(fn ($v) => ['type' => 'verification', 'id' => $v->id, 'title' => $v->submitted_name, 'subtitle' => (string) $v->status]);
            $results = array_merge($results, $vrs->toArray());
        }

        return response()->json(['results' => $results]);
    }
}

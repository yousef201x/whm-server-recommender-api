<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class ServerController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255', 'unique:servers,name'],
            'hostname'  => ['required', 'string', 'max:255', 'unique:servers,hostname'],
            'user'      => ['required', 'string', 'max:255'],
            'token' => ['required', 'string', 'unique:servers,token'],
            'is_active' => ['', 'boolean'],
        ]);

        $server = Server::create($validated);

        return response()->json([
            'message' => 'Server created successfully.',
        ], 200);
    }

    private function getServerLoad(Server $server): int
    {

        $response = Http::withHeaders([
            'Authorization' => 'whm ' . $server->user . ':' . $server->token,
        ])->get("https://{$server->hostname}:2087/json-api/listaccts?api.version=1");

        if ($response->successful()) {
            return count($response->json('data.acct') ?? []);
        }

        return PHP_INT_MAX;
    }

    public function recommend()
    {
        $servers = Server::where('is_active', true)->get();

        $loads = [];

        foreach ($servers as $server) {
            $loads[$server->name] = [
                'users' => $this->getServerLoad($server)
            ];
        }

        $sorted = collect($loads)->sortBy('users')->values();

        $recommended = $sorted->first();
        $others = $sorted->slice(1)->values();

        return response()->json([
            'recommended' => $recommended,
            'other' => $others,
        ]);
    }
}

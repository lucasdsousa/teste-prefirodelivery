<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::whereNull('parent_id')
            ->with('children.children.children')
            ->get();

        return response()->json($categorias);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categorias,id'
        ]);

        $categoria = Categoria::create($validated);
        return response()->json($categoria, 201);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categorias,id'
        ]);

        if ($request->parent_id == $categoria->id) {
            return response()->json(['error' => 'Categoria não pode ser pai dela mesma'], 422);
        }

        $categoria->update($validated);
        return response()->json($categoria);
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return response()->json(null, 204);
    }
}

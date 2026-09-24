<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok & Produk - Rental PS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    {{-- PANGGIL NAVBAR --}}
    @include('layouts.navigation')

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Flash Alert Success --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        {{-- Grid Form & Table --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Form Tambah Produk --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 md:col-span-1">
                <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wider">Tambah Produk Baru</h3>
                <form action="{{ route('products.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Produk / FnB</label>
                        <input type="text" name="name" required placeholder="Contoh: Indomie Goreng" class="w-full text-sm border-gray-300 rounded-lg p-2.5 border focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kategori</label>
                    <select name="category" required class="w-full text-sm border-gray-300 rounded-lg p-2.5 border focus:ring-2 focus:ring-indigo-500">
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Snack">Snack</option>
                    </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Harga Modal (HPP)</label>
                        <input type="number" name="cost_price" required placeholder="0" class="w-full text-sm border-gray-300 rounded-lg p-2.5 border focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Harga Jual</label>
                        <input type="number" name="price" required placeholder="0" class="w-full text-sm border-gray-300 rounded-lg p-2.5 border focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Stok Awal</label>
                        <input type="number" name="stock" required placeholder="0" class="w-full text-sm border-gray-300 rounded-lg p-2.5 border focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2.5 rounded-lg font-semibold transition">
                        + Simpan Produk
                    </button>
                </form>
            </div>

            {{-- Tabel Daftar Produk --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden md:col-span-2">
                <div class="p-4 border-b border-gray-200 font-bold text-gray-700 flex justify-between items-center bg-gray-50">
                    <span>Daftar Stok FnB</span>
                    <span class="text-xs bg-indigo-100 text-indigo-700 font-bold px-3 py-1 rounded-full">Total: {{ $products->count() }} Item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 uppercase tracking-wider border-b">
                                <th class="p-3">Nama Produk</th>
                                <th class="p-3">Harga Modal</th>
                                <th class="p-3">Harga Jual</th>
                                <th class="p-3">Margin/Profit</th>
                                <th class="p-3 text-center">Stok</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @if($products->count() > 0)
                            @foreach($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-semibold text-gray-800">{{ $product->name }}</td>
                                <td class="p-3 text-gray-600">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                                <td class="p-3 font-medium text-gray-800">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td class="p-3 font-semibold text-emerald-600">
                                    Rp {{ number_format($product->price - $product->cost_price, 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-center">
                                    @if($product->stock <= 0)
                                        <span class="bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded text-[10px]">HABIS</span>
                                    @elseif($product->stock <= 5)
                                        <span class="bg-amber-100 text-amber-700 font-bold px-2 py-0.5 rounded text-[10px]">{{ $product->stock }} (Tipis)</span>
                                    @else
                                        <span class="bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded text-[10px]">{{ $product->stock }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right space-x-1">
                                    <button onclick="editProduct({{ json_encode($product) }})" class="bg-amber-500 hover:bg-amber-600 text-white px-2 py-1 rounded text-[11px] font-semibold">
                                        Edit / Restock
                                    </button>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-[11px] font-semibold">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500 italic">Belum ada data produk FnB.</td>
                            </tr>
                        @endif
                    </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Edit / Restock Produk --}}
    <div id="editModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-5 space-y-4">
            <h3 class="font-bold text-gray-800 text-base border-b pb-2">Edit / Restock Produk</h3>
            <form id="editForm" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Produk</label>
                    <input type="text" id="edit_name" name="name" required class="w-full text-sm border-gray-300 rounded-lg p-2 border">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Harga Modal (HPP)</label>
                        <input type="number" id="edit_cost_price" name="cost_price" required class="w-full text-sm border-gray-300 rounded-lg p-2 border">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Harga Jual</label>
                        <input type="number" id="edit_price" name="price" required class="w-full text-sm border-gray-300 rounded-lg p-2 border">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jumlah Stok</label>
                    <input type="number" id="edit_stock" name="stock" required class="w-full text-sm border-gray-300 rounded-lg p-2 border">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 hover:bg-gray-600 text-white text-xs px-3 py-2 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-2 rounded-lg font-semibold">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editProduct(product) {
            document.getElementById('editForm').action = `/products/${product.id}`;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_cost_price').value = product.cost_price;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</body>
</html>
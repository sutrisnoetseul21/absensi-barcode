<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="close"></div>

                <!-- Modal panel -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Input Manual Absensi: {{ $studentName }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Pilih status absensi untuk siswa ini. Catatan: Data dari Kios Scan tidak dapat ditimpa.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form wire:submit.prevent="save" class="mt-5 sm:mt-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                            <input wire:model.blur="date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select wire:model.change="status" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="hadir">Hadir</option>
                                <option value="telat">Telat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpa">Alpa</option>
                            </select>
                            @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        @if($status === 'telat')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Menit Keterlambatan</label>
                            <input wire:model.blur="lateMinutes" type="number" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('lateMinutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Keterangan / Catatan Tambahan</label>
                            <textarea wire:model.blur="note" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            @error('note') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button type="button" wire:click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

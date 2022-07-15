<div class="p-6 sm:px-20 bg-white border-b border-gray-200">
    <div class="mt-8 text-2xl flex justify-between">
        <div class="page-head">
            Contracts
        </div>
        <div class="search-field">
            <input autocomplete="off" wire:model.debounce.500ms="q" type="search" placeholder="Search"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
        </div>
    </div>
    {{-- {{ $query }} --}}

    <div class="mt-6">
        @if ($contracts->count())
            <div class="flex flex-col mt-4">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contract ID
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Data
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" x-max="1">
                                    @foreach ($contracts as $contract)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $contract->contractId }}
                                            </td>
                                            <td class="px-6 py-4 break-all">
                                                {{ $contract->contractData }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $contract->status == 0 ? 'Not Synced' : 'Synced' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-4 bg-white overflow-hidden">
                <div class="header-container container flex justify-between p-10">
                    <p class="w-full text-center">No Data Found</p>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $contracts->links() }}
    </div>

</div>

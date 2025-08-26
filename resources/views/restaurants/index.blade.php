@extends('layouts.main-layout')

@section('title', 'Restaurants')


@section('content')
    <!-- ===== Main Content Start ===== -->
    <main>
        <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Basic Tables` }">
                @include('partials.breadcrumb')
            </div>
            <!-- Breadcrumb End -->

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif


            <div class="space-y-5 sm:space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="px-5 py-4 sm:px-6 sm:py-5">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Basic Table 1
                        </h3>
                    </div>
                    <div class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                        <!-- ====== Table Six Start -->
                        <div
                            class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="max-w-full overflow-x-auto">
                                <table class="min-w-full">
                                    <!-- table header start -->
                                    <thead>
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <th class="px-5 py-3 sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                        Name
                                                    </p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                        Email
                                                    </p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                        Phone
                                                    </p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                        Address
                                                    </p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                        Delivary Zones
                                                    </p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                        Orders
                                                    </p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                        Actions
                                                    </p>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <!-- table header end -->
                                    <!-- table body start -->
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach ($restaurants as $restaurant)
                                            <tr>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <div class="flex items-center gap-3">
                                                            <div>
                                                                <span
                                                                    class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                                    {{ $restaurant->name }}
                                                                </span>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $restaurant->email }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $restaurant->phone }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $restaurant->address }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $restaurant->delivery_zones_count }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $restaurant->orders_count }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="flex gap-2">
                                                        <!-- Delivery Zones -->
                                                        <a href="{{ route('restaurants.delivery-zones', $restaurant->id) }}"
                                                            class="px-2 py-1 text-white bg-blue-500 rounded hover:bg-blue-600"
                                                            title="Delivery Zones">
                                                            <!-- Map Pin Icon -->
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="w-3 h-5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M12 21c4.97-6.16 7.5-10.61 7.5-13.5A7.5 7.5 0 0 0 12 0a7.5 7.5 0 0 0-7.5 7.5C4.5 10.39 7.03 14.84 12 21z" />
                                                                <circle cx="12" cy="7.5" r="2.5"
                                                                    fill="currentColor" />
                                                            </svg>
                                                        </a>

                                                        <!-- View -->
                                                        <a href="{{ route('restaurants.show', $restaurant->id) }}"
                                                            class="px-2 py-1 text-white bg-gray-500 rounded hover:bg-gray-600"
                                                            title="View">
                                                            <!-- Eye Icon -->
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="w-3 h-5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                                                                <circle cx="12" cy="12" r="3" />
                                                            </svg>
                                                        </a>

                                                        <!-- Edit -->
                                                        <a href="{{ route('restaurants.edit', $restaurant->id) }}"
                                                            class="px-2 py-1 text-white bg-yellow-500 rounded hover:bg-yellow-600"
                                                            title="Edit">
                                                            <!-- Pencil Icon -->
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="w-3 h-5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M16.862 3.487a2.25 2.25 0 0 1 3.182 3.182L7.5 19.313 3 21l1.687-4.5L16.862 3.487z" />
                                                            </svg>
                                                        </a>

                                                        <!-- Delete -->
                                                        <form action="{{ route('restaurants.destroy', $restaurant->id) }}"
                                                            method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                                                title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this restaurant?')">
                                                                <!-- Trash Icon -->
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                                    stroke="currentColor" class="w-3 h-5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M6 7.5h12m-9 0v10.5m6-10.5v10.5M9 4.5h6a.75.75 0 0 1 .75.75v1.5h-7.5v-1.5a.75.75 0 0 1 .75-.75z" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ====== Table Six End -->
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

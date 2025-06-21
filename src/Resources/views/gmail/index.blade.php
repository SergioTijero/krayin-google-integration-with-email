<x-admin::layouts>
    <x-slot:title>
        @lang('google::app.gmail.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="google.gmail" />
            </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Sync Button -->
                <a
                    href="{{ route('admin.google.gmail.sync') }}"
                    class="primary-button"
                >
                    @lang('google::app.gmail.buttons.sync-now')
                </a>

                <!-- Compose Button -->
                <a
                    href="{{ route('admin.google.gmail.compose') }}"
                    class="primary-button"
                >
                    @lang('google::app.gmail.buttons.compose')
                </a>
            </div>
        </div>

        <!-- Gmail Interface -->
        <div class="flex gap-4">
            <!-- Sidebar -->
            <div class="w-64 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <!-- Folder Navigation -->
                <nav class="space-y-2">
                    <a
                        href="{{ route('admin.google.gmail.index', ['folder' => 'inbox']) }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->get('folder', 'inbox') === 'inbox' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        <i class="icon-inbox mr-3"></i>
                        @lang('google::app.gmail.folders.inbox')
                        @if($account && $account->getUnreadGmailCount() > 0)
                            <span class="ml-auto bg-blue-500 text-white text-xs rounded-full px-2 py-1">
                                {{ $account->getUnreadGmailCount() }}
                            </span>
                        @endif
                    </a>

                    <a
                        href="{{ route('admin.google.gmail.index', ['folder' => 'sent']) }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->get('folder') === 'sent' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        <i class="icon-send mr-3"></i>
                        @lang('google::app.gmail.folders.sent')
                    </a>

                    <a
                        href="{{ route('admin.google.gmail.index', ['folder' => 'drafts']) }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->get('folder') === 'drafts' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        <i class="icon-draft mr-3"></i>
                        @lang('google::app.gmail.folders.drafts')
                    </a>

                    <a
                        href="{{ route('admin.google.gmail.index', ['folder' => 'trash']) }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->get('folder') === 'trash' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        <i class="icon-trash mr-3"></i>
                        @lang('google::app.gmail.folders.trash')
                    </a>
                </nav>
            </div>

            <!-- Email List -->
            <div class="flex-1 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <!-- Email List Header -->
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        @lang('google::app.gmail.folders.' . $folder)
                    </h3>
                </div>

                <!-- Email Messages -->
                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($messages as $message)
                        <div class="group relative">
                            <a
                                href="{{ route('admin.google.gmail.show', $message->google_message_id) }}"
                                class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 {{ !$message->is_read ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                            >
                                <div class="flex items-center space-x-4">
                                    <!-- Sender/Status Icons -->
                                    <div class="flex-shrink-0">
                                        @if(!$message->is_read)
                                            <div class="h-2 w-2 bg-blue-500 rounded-full"></div>
                                        @endif
                                        @if($message->is_starred)
                                            <i class="icon-star text-yellow-400"></i>
                                        @endif
                                    </div>

                                    <!-- Email Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $message->from }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $message->internal_date ? $message->internal_date->diffForHumans() : '' }}
                                            </p>
                                        </div>
                                        <p class="text-sm text-gray-900 dark:text-white truncate mt-1">
                                            {{ $message->subject }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-1">
                                            {{ $message->preview }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            onclick="event.preventDefault(); event.stopPropagation(); deleteMessage('{{ $message->google_message_id }}')"
                                            class="text-red-600 hover:text-red-800"
                                        >
                                            <i class="icon-delete"></i>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <p class="text-gray-500 dark:text-gray-400">
                                @lang('admin::app.common.no-records-found')
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($messages->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                        {{ $messages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function deleteMessage(messageId) {
                if (confirm('Are you sure you want to delete this email?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.google.gmail.delete", ":id") }}'.replace(':id', messageId);
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    
                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        </script>
    @endpush
</x-admin::layouts>

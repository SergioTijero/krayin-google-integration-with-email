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
                
                <div class="text-xl font-bold dark:text-gray-300">
                    @lang('google::app.gmail.title')
                </div>
                
                <p class="text-gray-600 dark:text-gray-400">
                    @lang('google::app.gmail.description')
                </p>
            </div>
        </div>

        <!-- Gmail Configuration -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                @lang('google::app.gmail.configuration')
            </h3>

            @forelse($accounts as $account)
                <div class="border rounded-lg p-4 mb-4 {{ $account->gmail_enabled ? 'border-green-300 bg-green-50' : 'border-gray-300' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Account Info -->
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">
                                    {{ $account->name }}
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $account->email ?? $account->google_id }}
                                </p>
                                
                                <!-- Status -->
                                <div class="flex items-center mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $account->gmail_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $account->gmail_enabled ? trans('google::app.gmail.status.enabled') : trans('google::app.gmail.status.disabled') }}
                                    </span>
                                    
                                    @if($account->hasGmailPermissions())
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            @lang('google::app.gmail.status.permissions-granted')
                                        </span>
                                    @else
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            @lang('google::app.gmail.status.permissions-missing')
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            @if($account->hasGmailPermissions())
                                @if($account->gmail_enabled)
                                    <!-- Test Email Form -->
                                    <x-admin::form
                                        :action="route('admin.google.gmail.test', $account->id)"
                                        method="POST"
                                        class="inline-flex"
                                    >
                                        <div class="flex items-center space-x-2">
                                            <input
                                                type="email"
                                                name="test_email"
                                                placeholder="@lang('google::app.gmail.test-email-placeholder')"
                                                class="text-sm border border-gray-300 rounded px-3 py-1"
                                                required
                                            >
                                            <button type="submit" class="secondary-button">
                                                @lang('google::app.gmail.test-email')
                                            </button>
                                        </div>
                                    </x-admin::form>

                                    <!-- Disable Button -->
                                    <x-admin::form
                                        :action="route('admin.google.gmail.disable', $account->id)"
                                        method="POST"
                                        class="inline-flex"
                                    >
                                        <button type="submit" class="secondary-button">
                                            @lang('google::app.gmail.disable')
                                        </button>
                                    </x-admin::form>
                                @else
                                    <!-- Enable Button -->
                                    <x-admin::form
                                        :action="route('admin.google.gmail.enable', $account->id)"
                                        method="POST"
                                        class="inline-flex"
                                    >
                                        <button type="submit" class="primary-button">
                                            @lang('google::app.gmail.enable')
                                        </button>
                                    </x-admin::form>
                                @endif
                            @else
                                <p class="text-sm text-red-600">
                                    @lang('google::app.gmail.reauth-required')
                                    <a href="{{ route('admin.google.index') }}" class="underline">
                                        @lang('google::app.gmail.reconnect')
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">
                        @lang('google::app.gmail.no-accounts')
                    </p>
                    <a href="{{ route('admin.google.index') }}" class="primary-button mt-4">
                        @lang('google::app.gmail.connect-account')
                    </a>
                </div>
            @endforelse

            <!-- Instructions -->
            @if($accounts->count() > 0)
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-medium text-blue-900 mb-2">
                        @lang('google::app.gmail.instructions.title')
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• @lang('google::app.gmail.instructions.step1')</li>
                        <li>• @lang('google::app.gmail.instructions.step2')</li>
                        <li>• @lang('google::app.gmail.instructions.step3')</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-admin::layouts>

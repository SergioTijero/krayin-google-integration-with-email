<x-admin::layouts>
    <x-slot:title>
        @lang('google::app.gmail.compose')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="google.gmail.compose" />
            </div>
        </div>

        <!-- Compose Form -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <x-admin::form
                :action="route('admin.google.gmail.send')"
                method="POST"
                enctype="multipart/form-data"
            >
                <div class="space-y-6">
                    <!-- To Field -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('google::app.gmail.fields.to')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="to"
                            :value="old('to')"
                            :placeholder="trans('google::app.gmail.placeholders.to')"
                            rules="required"
                        />

                        <x-admin::form.control-group.error control-name="to" />
                    </x-admin::form.control-group>

                    <!-- CC Field -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('google::app.gmail.fields.cc')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="cc"
                            :value="old('cc')"
                            :placeholder="trans('google::app.gmail.placeholders.cc')"
                        />

                        <x-admin::form.control-group.error control-name="cc" />
                    </x-admin::form.control-group>

                    <!-- BCC Field -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('google::app.gmail.fields.bcc')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="bcc"
                            :value="old('bcc')"
                            :placeholder="trans('google::app.gmail.placeholders.bcc')"
                        />

                        <x-admin::form.control-group.error control-name="bcc" />
                    </x-admin::form.control-group>

                    <!-- Subject Field -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('google::app.gmail.fields.subject')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="subject"
                            :value="old('subject')"
                            :placeholder="trans('google::app.gmail.placeholders.subject')"
                            rules="required"
                        />

                        <x-admin::form.control-group.error control-name="subject" />
                    </x-admin::form.control-group>

                    <!-- Body Field -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('google::app.gmail.fields.body')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="body"
                            :value="old('body')"
                            :placeholder="trans('google::app.gmail.placeholders.body')"
                            rules="required"
                            rows="10"
                            id="email-body"
                        />

                        <x-admin::form.control-group.error control-name="body" />
                    </x-admin::form.control-group>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-x-2.5">
                        <a
                            href="{{ route('admin.google.gmail.index') }}"
                            class="secondary-button"
                        >
                            @lang('google::app.gmail.buttons.cancel')
                        </a>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('google::app.gmail.buttons.send')
                        </button>
                    </div>
                </div>
            </x-admin::form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Initialize TinyMCE if available
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#email-body',
                    height: 300,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | ' +
                        'bold italic backcolor | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist outdent indent | ' +
                        'removeformat | help',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
                });
            }
        </script>
    @endpush
</x-admin::layouts>

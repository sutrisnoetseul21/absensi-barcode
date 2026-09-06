@php
    $id = $getId();
    $statePath = $getStatePath();
    $height = $getHeight();
    $uploadUrl = $getUploadUrl();
    $uploadDir = $getUploadDirectory();
    $menubar = $getMenubar();
    $uniqueSuffix = strtolower(\Illuminate\Support\Str::random(8));
    $cleanId = 'tinymce_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $id) . '_' . $uniqueSuffix;
    $baseUrl = asset('vendor/tinymce');
@endphp

@once
    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
    <style>
        .tinymce-wrap .tox-tinymce {
            border: none !important;
            border-radius: 0 !important;
        }
        .tox-tinymce-aux, .tox-silver-sink {
            z-index: 99999 !important;
        }
    </style>
@endonce

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        wire:key="{{ $cleanId }}"
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
            editorId: @js($cleanId),
            height: @js($height),
            uploadUrl: @js($uploadUrl),
            uploadDir: @js($uploadDir),
            menubar: @js($menubar),
            csrfToken: @js(csrf_token()),
            baseUrl: @js($baseUrl),
            editorInstance: null,
            isInitializing: false,

            init() {
                let initLoop = () => {
                    if (this.editorInstance) {
                        return;
                    }
                    if (window.tinymce) {
                        this.initEditor();
                    } else {
                        setTimeout(initLoop, 50);
                    }
                };
                this.$nextTick(initLoop);
                setTimeout(initLoop, 50);
                setTimeout(initLoop, 200);

                this.$watch('state', (val) => {
                    if (this.editorInstance && this.editorInstance.initialized) {
                        if (this.editorInstance.hasFocus && this.editorInstance.hasFocus()) {
                            return;
                        }
                        if (val !== this.editorInstance.getContent()) {
                            this.editorInstance.setContent(val || '');
                        }
                    }
                });

                if (typeof this.$cleanup === 'function') {
                    this.$cleanup(() => this.destroy());
                }
            },

            initEditor() {
                if (this.isInitializing || this.editorInstance) {
                    return;
                }

                let el = this.$refs.editorTextarea;
                if (!el) {
                    el = document.getElementById(this.editorId);
                }
                if (!el) return;

                if (!el.id) {
                    el.id = this.editorId;
                }

                if (window.tinymce && window.tinymce.get(el.id)) {
                    window.tinymce.get(el.id).remove();
                }

                this.isInitializing = true;
                let self = this;
                const isDarkMode = document.documentElement.classList.contains('dark');
                const baseUrl = this.baseUrl;
                window.tinymce.baseURL = baseUrl;

                let form = el.closest('form');
                if (form && !form._tinymce_submit_bound) {
                    form._tinymce_submit_bound = true;
                    form.addEventListener('submit', () => {
                        if (window.tinymce) {
                            window.tinymce.triggerSave();
                        }
                    });
                }

                tinymce.init({
                    target: el,
                    base_url: baseUrl,
                    license_key: 'gpl',
                    height: self.height,
                    menubar: self.menubar,
                    branding: false,
                    promotion: false,
                    resize: true,
                    relative_urls: false,
                    remove_script_host: false,
                    skin_url: isDarkMode ? `${baseUrl}/skins/ui/oxide-dark` : `${baseUrl}/skins/ui/oxide`,
                    content_css: isDarkMode ? `${baseUrl}/skins/content/dark/content.min.css` : `${baseUrl}/skins/content/default/content.min.css`,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                        'searchreplace', 'visualblocks', 'code',
                        'table', 'help', 'wordcount', 'emoticons', 'codesample',
                        'nonbreaking', 'pagebreak', 'insertdatetime', 'media'
                    ],
                    toolbar:
                        'undo redo | blocks | ' +
                        'bold italic underline strikethrough | ' +
                        'forecolor backcolor | ' +
                        'alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist outdent indent | ' +
                        'image media table | ' +
                        'removeformat | code help',
                    paste_data_images: true,
                    automatic_uploads: true,
                    image_title: true,
                    image_advtab: true,
                    image_caption: false,
                    object_resizing: true,
                    image_toolbar: 'rotateleft rotateright | flipv fliph | editimage imagetextalternative',
                    content_style: `
                        body { 
                            font-family: Inter, ui-sans-serif, system-ui, -apple-system, sans-serif; 
                            font-size: 14px; 
                            color: ${isDarkMode ? '#e2e8f0' : '#1e293b'}; 
                            background-color: ${isDarkMode ? '#0f172a' : '#ffffff'};
                            padding: 12px 16px; 
                            line-height: 1.6;
                        }
                        img { max-width: 100%; height: auto; border-radius: 8px; margin: 8px 0; }
                        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
                        table, th, td { border: 1px solid ${isDarkMode ? '#334155' : '#cbd5e1'}; padding: 8px; }
                        blockquote { border-left: 4px solid #f59e0b; margin-left: 0; padding-left: 14px; color: ${isDarkMode ? '#94a3b8' : '#64748b'}; font-style: italic; }
                    `,
                    images_upload_handler: (blobInfo, progress) => {
                        return new Promise((resolve, reject) => {
                            const formData = new FormData();
                            formData.append('file', blobInfo.blob(), blobInfo.filename());
                            formData.append('_token', self.csrfToken);
                            formData.append('folder', self.uploadDir);

                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', self.uploadUrl);

                            xhr.upload.onprogress = (e) => {
                                if (e.lengthComputable) progress((e.loaded / e.total) * 100);
                            };

                            xhr.onload = () => {
                                if (xhr.status < 200 || xhr.status >= 300) {
                                    let errorMsg = 'Upload gagal: HTTP ' + xhr.status;
                                    try {
                                        const errJson = JSON.parse(xhr.responseText);
                                        if (errJson.message) errorMsg = errJson.message;
                                    } catch(e) {}
                                    reject({ message: errorMsg, remove: true });
                                    return;
                                }

                                let json;
                                try {
                                    json = JSON.parse(xhr.responseText);
                                } catch (e) {
                                    reject({ message: 'Respon server tidak valid', remove: true });
                                    return;
                                }

                                const url = json.location || json.url;
                                if (!url) {
                                    reject({ message: 'URL gambar tidak ditemukan', remove: true });
                                    return;
                                }

                                resolve(url);
                            };

                            xhr.onerror = () => {
                                reject({ message: 'Koneksi ke server gagal', remove: true });
                            };

                            xhr.send(formData);
                        });
                    },
                    setup: (editor) => {
                        self.editorInstance = editor;
                        self.isInitializing = false;

                        editor.on('init', () => {
                            let initialContent = self.state;
                            if (!initialContent && el.value) {
                                initialContent = el.value;
                                self.state = el.value;
                            }
                            if (initialContent) {
                                editor.setContent(initialContent);
                            }
                        });

                        editor.on('change input undo redo keyup ExecCommand blur SetContent', () => {
                            self.state = editor.getContent();
                        });
                    }
                }).catch(() => {
                    self.isInitializing = false;
                });
            },

            destroy() {
                if (this.editorInstance) {
                    try {
                        this.editorInstance.remove();
                    } catch(e) {}
                    this.editorInstance = null;
                }
                let el = this.$refs.editorTextarea;
                if (el && window.tinymce && window.tinymce.get(el.id)) {
                    try {
                        window.tinymce.get(el.id).remove();
                    } catch(e) {}
                }
                this.isInitializing = false;
            }
        }"
        x-on:destroy.window="destroy()"
        class="tinymce-wrap rounded-2xl overflow-hidden border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm transition-all focus-within:ring-2 focus-within:ring-amber-500/30 focus-within:border-amber-500"
    >
        <textarea
            id="{{ $cleanId }}"
            x-ref="editorTextarea"
            class="w-full border-0 p-4 outline-none text-sm text-slate-800 dark:text-slate-200 bg-transparent block"
            style="min-height: {{ $height }}px;"
        >{{ $getState() }}</textarea>
    </div>
</x-dynamic-component>

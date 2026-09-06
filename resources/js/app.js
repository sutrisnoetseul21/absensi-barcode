// Import Local Fonts (Plus Jakarta Sans & Inter)
import '@fontsource/plus-jakarta-sans/400.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';
import '@fontsource/plus-jakarta-sans/800.css';
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import '@fontsource/inter/800.css';

// Import Local Icons (FontAwesome)
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import Local Swiper & GLightbox Styles
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/autoplay';
import 'glightbox/dist/css/glightbox.min.css';

// Import Local Trix Editor (NPM)
import 'trix';
import 'trix/dist/trix.css';

import './bootstrap';
import Chart from 'chart.js/auto';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import Swiper from 'swiper';
import { Autoplay, Pagination, Navigation } from 'swiper/modules';
import GLightbox from 'glightbox';

// Automatically configure Swiper with essential modules
Swiper.use([Autoplay, Pagination, Navigation]);

// Expose to window for inline scripts & Alpine.js
window.Chart = Chart;
window.FullCalendar = { Calendar, dayGridPlugin, interactionPlugin };
window.Swiper = Swiper;
window.GLightbox = GLightbox;

// Import TinyMCE
import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver/theme';
import 'tinymce/icons/default/icons';
import 'tinymce/models/dom/model';

// TinyMCE Plugins
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/code';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/media';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/visualblocks';

// TinyMCE Skins
import 'tinymce/skins/ui/oxide/skin.min.css';
import contentUiCss from 'tinymce/skins/ui/oxide/content.min.css?inline';
import contentCss from 'tinymce/skins/content/default/content.min.css?inline';

window.tinymce = tinymce;

window.portalWebTinyMCE = function(model, customId = null, customHeight = 450) {
    return {
        value: model,
        editorId: null,
        editorInstance: null,
        init() {
            this.$nextTick(() => {
                const el = this.$refs.editor;
                if (!el) return;

                this.editorId = customId || el.id || ('tinymce_' + Math.random().toString(36).substring(2, 9));
                el.id = this.editorId;

                const initTiny = () => {
                    if (!window.tinymce) {
                        setTimeout(initTiny, 50);
                        return;
                    }

                    if (window.tinymce.get(this.editorId)) {
                        window.tinymce.get(this.editorId).remove();
                    }

                    const form = el.closest('form');
                    if (form && !form._tinymce_submit_bound) {
                        form._tinymce_submit_bound = true;
                        form.addEventListener('submit', () => {
                            if (this.editorInstance) {
                                this.value = this.editorInstance.getContent();
                            }
                        });
                    }

                    window.tinymce.init({
                        license_key: 'gpl',
                        target: el,
                        skin: false,
                        content_css: false,
                        content_style: [
                            contentUiCss, 
                            contentCss, 
                            'img { max-width: 100%; height: auto; border-radius: 8px; margin: 8px 0; } ' +
                            'body { font-family: Inter, system-ui, -apple-system, sans-serif; font-size: 14px; padding: 14px; line-height: 1.7; color: #334155; } ' +
                            'table { border-collapse: collapse; width: 100%; margin: 12px 0; } ' +
                            'table, th, td { border: 1px solid #cbd5e1; padding: 8px 12px; } ' +
                            'blockquote { border-left: 4px solid #7c3aed; margin-left: 0; padding-left: 14px; color: #64748b; font-style: italic; }'
                        ].join('\n'),
                        object_resizing: true,
                        resize_img_proportional: true,
                        height: customHeight,
                        menubar: true,
                        plugins: 'image link lists table code wordcount fullscreen media preview searchreplace visualblocks advlist autolink',
                        toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image media link table | code preview fullscreen removeformat',
                        image_title: true,
                        automatic_uploads: true,
                        file_picker_types: 'image',
                        file_picker_callback: (callback, value, meta) => {
                            if (meta.filetype === 'image') {
                                const input = document.createElement('input');
                                input.setAttribute('type', 'file');
                                input.setAttribute('accept', 'image/*');
                                input.onchange = function () {
                                    const file = this.files[0];
                                    if (!file) return;

                                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                    if (csrfToken) {
                                        const formData = new FormData();
                                        formData.append('file', file);
                                        formData.append('_token', csrfToken);
                                        formData.append('folder', 'web-profil/editor');

                                        const xhr = new XMLHttpRequest();
                                        xhr.open('POST', '/admin/tinymce-upload');
                                        xhr.onload = () => {
                                            if (xhr.status >= 200 && xhr.status < 300) {
                                                try {
                                                    const json = JSON.parse(xhr.responseText);
                                                    const url = json.location || json.url;
                                                    if (url) {
                                                        callback(url, { alt: file.name });
                                                        return;
                                                    }
                                                } catch (e) {}
                                            }
                                            const reader = new FileReader();
                                            reader.onload = function () {
                                                callback(reader.result, { alt: file.name });
                                            };
                                            reader.readAsDataURL(file);
                                        };
                                        xhr.onerror = () => {
                                            const reader = new FileReader();
                                            reader.onload = function () {
                                                callback(reader.result, { alt: file.name });
                                            };
                                            reader.readAsDataURL(file);
                                        };
                                        xhr.send(formData);
                                    } else {
                                        const reader = new FileReader();
                                        reader.onload = function () {
                                            callback(reader.result, { alt: file.name });
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                };
                                input.click();
                            }
                        },
                        images_upload_handler: (blobInfo, progress) => new Promise((resolve) => {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            if (csrfToken) {
                                const formData = new FormData();
                                formData.append('file', blobInfo.blob(), blobInfo.filename());
                                formData.append('_token', csrfToken);
                                formData.append('folder', 'web-profil/editor');

                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', '/admin/tinymce-upload');

                                xhr.upload.onprogress = (e) => {
                                    if (e.lengthComputable) progress((e.loaded / e.total) * 100);
                                };

                                xhr.onload = () => {
                                    if (xhr.status >= 200 && xhr.status < 300) {
                                        try {
                                            const json = JSON.parse(xhr.responseText);
                                            const url = json.location || json.url;
                                            if (url) {
                                                resolve(url);
                                                return;
                                            }
                                        } catch (e) {}
                                    }
                                    resolve('data:' + blobInfo.blob().type + ';base64,' + blobInfo.base64());
                                };

                                xhr.onerror = () => {
                                    resolve('data:' + blobInfo.blob().type + ';base64,' + blobInfo.base64());
                                };

                                xhr.send(formData);
                            } else {
                                resolve('data:' + blobInfo.blob().type + ';base64,' + blobInfo.base64());
                            }
                        }),
                        setup: (editor) => {
                            this.editorInstance = editor;

                            editor.on('init', () => {
                                if (this.value) {
                                    editor.setContent(this.value);
                                }
                            });

                            editor.on('change keyup blur undo redo SetContent ExecCommand', () => {
                                this.value = editor.getContent();
                            });
                        }
                    });
                };

                setTimeout(initTiny, 100);

                this.$watch('value', (newVal) => {
                    if (this.editorInstance && this.editorInstance.initialized) {
                        if (this.editorInstance.hasFocus && this.editorInstance.hasFocus()) {
                            return;
                        }
                        if ((newVal || '') !== this.editorInstance.getContent()) {
                            this.editorInstance.setContent(newVal || '');
                        }
                    }
                });
            });
        },
        destroy() {
            if (this.editorId && window.tinymce && window.tinymce.get(this.editorId)) {
                window.tinymce.get(this.editorId).remove();
            }
        }
    };
};

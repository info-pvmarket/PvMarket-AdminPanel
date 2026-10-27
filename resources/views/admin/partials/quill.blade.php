{{-- Quill plus the CMS image rules (TASK-6.4). Include this instead of the bare quill.min.js tag. --}}
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>window.PV_EDITOR_IMAGE_UPLOAD_URL = @json(route('admin.editor-images.store'));</script>
<script src="{{ asset('js/admin-quill.js') }}?v=2"></script>

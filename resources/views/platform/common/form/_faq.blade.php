<div class="card card-flush py-4">

    <div class=" pt-0">

        <div class="mb-10">
            <label class="form-label">FAQ content</label>
            {{-- <div id="faq_content_div" name="faq_content" class="min-h-200px mb-2">{!! $info->faq_content ?? '' !!}</div> --}}
            <textarea name="faq_content" class="form-control" id="faq_content" cols="30" rows="2">{{ $info->faq_content ?? '' }}</textarea>
        </div>

    </div>
</div>
  <!-- Initialize Quill editor -->
<script>
    // var quill = new Quill('#faq_content_div', {
    //   theme: 'snow'
    // });
    // quill.on('text-change', function(delta, oldDelta, source) {
    //     document.getElementById("faq_content").value = quill.root.innerHTML;
    // });
  </script>

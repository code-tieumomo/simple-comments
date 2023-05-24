<div>
    @if($errors->has('commentable_type'))
        <div class="alert alert-danger" role="alert">
            {{ $errors->first('commentable_type') }}
        </div>
    @endif
    @if($errors->has('commentable_id'))
        <div class="alert alert-danger" role="alert">
            {{ $errors->first('commentable_id') }}
        </div>
    @endif
    <form method="POST" action="{{ route('comments.store') }}">
        @csrf
        @honeypot
        <input type="hidden" name="commentable_type" value="\{{ get_class($model) }}"/>
        <input type="hidden" name="commentable_id" value="{{ $model->getKey() }}"/>

        {{-- Guest commenting --}}
        @if(isset($guest_commenting) and $guest_commenting == true)
            <div class="form-group">
                <label for="message">@lang('comments::comments.enter_your_name_here')</label>
                <input type="text" class="form-control @if($errors->has('guest_name')) is-invalid @endif"
                       name="guest_name"/>
                @error('guest_name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label for="message">@lang('comments::comments.enter_your_email_here')</label>
                <input type="email" class="form-control @if($errors->has('guest_email')) is-invalid @endif"
                       name="guest_email"/>
                @error('guest_email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        @endif

        <div class="relative">
            <div class="flex items-top gap-2">
                <img src="https://i.pravatar.cc/150?u={{ auth()->user()->email }}"
                     class=" w-[2.625rem] h-[2.625rem] rounded-full"/>
                <textarea id="comment-area"
                          class="grow h-[2.625rem] pl-3 pr-8 rounded-xl border-0 shadow-sm focus:ring-0 overflow-hidden resize-none @if($errors->has('message')) ring-2 ring-red-500 @endif"
                          name="message"
                          placeholder="@lang('comments::comments.enter_your_message_here')"></textarea>
            </div>
            <div class="invalid-feedback">
                @lang('comments::comments.your_message_is_required')
            </div>
            <small class="text-xs text-gray-400 ml-[3.125rem]">@lang('comments::comments.markdown_cheatsheet', ['url' => 'https://help.github.com/articles/basic-writing-and-formatting-syntax'])</small>
            <button type="submit" class="absolute right-2 bottom-8 focus:outline-0">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-primary opacity-70" fill="currentColor">
                    <path d="m21.426 11.095-17-8A1 1 0 0 0 3.03 4.242l1.212 4.849L12 12l-7.758 2.909-1.212 4.849a.998.998 0 0 0 1.396 1.147l17-8a1 1 0 0 0 0-1.81z"></path>
                </svg>
            </button>
        </div>
    </form>
</div>

<script>
    document.querySelector('#comment-area').addEventListener('input', function (e) {
        e.target.style.height = "2.625rem";
        e.target.style.height = (e.target.scrollHeight / 16) + "rem";

        if (e.target.value.length > 0) {
            e.target.classList.remove('ring-2', 'ring-red-500');
        } else {
            e.target.classList.add('ring-2', 'ring-red-500');
        }
    });
</script>

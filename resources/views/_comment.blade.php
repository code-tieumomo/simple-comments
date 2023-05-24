@inject('markdown', 'Parsedown')
@php
    // TODO: There should be a better place for this.
    $markdown->setSafeMode(true);
@endphp

<div id="comment-{{ $comment->getKey() }}" class="flex items-top gap-2">
    <img class="w-[2.625rem] h-[2.625rem] rounded-full"
         src="https://i.pravatar.cc/150?u={{ md5($comment->commenter->email ?? $comment->guest_email) }}"
         alt="{{ $comment->commenter->name ?? $comment->guest_name }} Avatar">
    <div class="flex flex-col text-sm grow">
        <h5 class="text-sm mb-0">{{ $comment->commenter->name ?? $comment->guest_name }}</h5>
        <div style="white-space: pre-wrap;" class="mb-1">{!! $markdown->line($comment->comment) !!}</div>

        <div class="flex gap-2">
            @can('like-comment', $comment)
                <a href="{{ route('comments.like', $comment->getKey()) }}"
                   onclick="event.preventDefault();document.getElementById('comment-like-form-{{ $comment->getKey() }}').submit();"
                   class="font-bold text-opacity-50 text-xs text-textPrimary focus:outline-0">@lang('comments::comments.like')</a>
                <form id="comment-like-form-{{ $comment->getKey() }}" method="POST"
                      action="{{ route('comments.like', $comment->getKey()) }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endcan
            @can('reply-to-comment', $comment)
                <button data-toggle="modal" data-target="#reply-modal-{{ $comment->getKey() }}"
                        class="font-bold text-opacity-50 text-xs text-textPrimary focus:outline-0">@lang('comments::comments.reply')</button>
            @endcan
            @can('edit-comment', $comment)
                <button data-toggle="modal" data-target="#comment-modal-{{ $comment->getKey() }}"
                        class="font-bold text-opacity-50 text-xs text-textPrimary focus:outline-0">@lang('comments::comments.edit')</button>
            @endcan
            @can('delete-comment', $comment)
                <a href="{{ route('comments.destroy', $comment->getKey()) }}"
                   onclick="event.preventDefault();document.getElementById('comment-delete-form-{{ $comment->getKey() }}').submit();"
                   class="font-bold text-opacity-50 text-xs text-red-500 focus:outline-0">@lang('comments::comments.delete')</a>
                <form id="comment-delete-form-{{ $comment->getKey() }}"
                      action="{{ route('comments.destroy', $comment->getKey()) }}" method="POST" style="display: none;">
                    @method('DELETE')
                    @csrf
                </form>
            @endcan
            <small class="text-opacity-50 text-xs text-textPrimary focus:outline-0">- {{ $comment->created_at->diffForHumans() }}</small>
        </div>

        @can('edit-comment', $comment)
            <div class="modal fade" id="comment-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('comments.update', $comment->getKey()) }}">
                            @method('PUT')
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">@lang('comments::comments.edit_comment')</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="message">@lang('comments::comments.update_your_message_here')</label>
                                    <textarea required class="form-control" name="message"
                                              rows="3">{{ $comment->comment }}</textarea>
                                    <small class="form-text text-muted">@lang('comments::comments.markdown_cheatsheet', ['url' => 'https://help.github.com/articles/basic-writing-and-formatting-syntax'])</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase"
                                        data-dismiss="modal">@lang('comments::comments.cancel')</button>
                                <button type="submit"
                                        class="btn btn-sm btn-outline-success text-uppercase">@lang('comments::comments.update')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('reply-to-comment', $comment)
            <div class="modal fade" id="reply-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('comments.reply', $comment->getKey()) }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">@lang('comments::comments.reply_to_comment')</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="message">@lang('comments::comments.enter_your_message_here')</label>
                                    <textarea required class="form-control" name="message" rows="3"></textarea>
                                    <small class="form-text text-muted">@lang('comments::comments.markdown_cheatsheet', ['url' => 'https://help.github.com/articles/basic-writing-and-formatting-syntax'])</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase"
                                        data-dismiss="modal">@lang('comments::comments.cancel')</button>
                                <button type="submit"
                                        class="btn btn-sm btn-outline-success text-uppercase">@lang('comments::comments.reply')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        <br/>{{-- Margin bottom --}}

            <?php
            if (!isset($indentationLevel)) {
                $indentationLevel = 1;
            } else {
                $indentationLevel++;
            }
            ?>

        {{-- Recursion for children --}}
        @if($grouped_comments->has($comment->getKey()) && $indentationLevel <= $maxIndentationLevel)
            {{-- TODO: Don't repeat code. Extract to a new file and include it. --}}
            @foreach($grouped_comments[$comment->getKey()] as $child)
                @include('comments::_comment', [
                    'comment' => $child,
                    'grouped_comments' => $grouped_comments
                ])
            @endforeach
        @endif
    </div>
</div>

{{-- Recursion for children --}}
@if($grouped_comments->has($comment->getKey()) && $indentationLevel > $maxIndentationLevel)
    {{-- TODO: Don't repeat code. Extract to a new file and include it. --}}
    @foreach($grouped_comments[$comment->getKey()] as $child)
        @include('comments::_comment', [
            'comment' => $child,
            'grouped_comments' => $grouped_comments
        ])
    @endforeach
@endif
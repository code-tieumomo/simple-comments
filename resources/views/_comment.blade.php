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
        <div class="rounded-xl py-1 px-2 bg-slate-200 relative">
            <h5 class="text-sm mb-0">{{ $comment->commenter->name ?? $comment->guest_name }}</h5>
            <div style="white-space: pre-wrap; word-break: break-word;">{!! $markdown->line($comment->comment) !!}</div>
            @if($comment->commentLikes->count() > 0)
                <div class="absolute right-2 -bottom-2 py-0.5 px-1 bg-white rounded-full text-xs shadow-md flex items-center gap-1 cursor-pointer"
                     data-toggle="modal" data-target="#commenter-modal-{{ $comment->getKey() }}">
                    <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 21h1V8H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2zM20 8h-7l1.122-3.368A2 2 0 0 0 12.225 2H12L7 7.438V21h11l3.912-8.596L22 12v-2a2 2 0 0 0-2-2z"></path>
                    </svg>
                    {{ $comment->commentLikes->count() }}
                </div>
                <div class="modal fade" id="commenter-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@lang('comments::comments.liker')</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body flex flex-col gap-2">
                                @foreach($comment->commentLikes as $like)
                                    <div class="flex items-center gap-2">
                                        <img src="https://i.pravatar.cc/150?u={{ $like->user->email }}"
                                             class=" w-6 h-6 rounded-full"/>
                                        {{ $like->user->name }}
                                        @if($like->user->id == \Illuminate\Support\Facades\Auth::user()->id)
                                            <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2 py-0.5 rounded">You</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase"
                                        data-dismiss="modal">@lang('comments::comments.cancel')</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex gap-2 mt-1">
            @can('like-comment', $comment)
                <a href="{{ route('comments.like', $comment->getKey()) }}"
                   onclick="event.preventDefault();document.getElementById('comment-like-form-{{ $comment->getKey() }}').submit();"
                   class="font-bold text-opacity-50 text-xs text-textPrimary focus:outline-0">
                    @if($comment->isLikedByLoggedInUser())
                        <span class="text-primary underline">
                            @lang('comments::comments.like')
                        </span>
                    @else
                        @lang('comments::comments.like')
                    @endif
                </a>
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
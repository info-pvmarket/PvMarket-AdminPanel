@extends('layouts.admin')

@section('title', 'Notifications')

@section('styles')
<style>
    .notifications-page {
        max-width: 1050px;
        margin: 0 auto;
    }

    .notifications-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .notifications-heading h1 {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .notifications-heading p {
        color: var(--muted);
        font-size: 13px;
    }

    .notifications-panel {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .notifications-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        background: #F8FAFC;
    }

    .notification-filters {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .notification-filter {
        padding: 7px 13px;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--muted);
        background: var(--white);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .notification-filter.active,
    .notification-filter:hover {
        color: var(--primary-d);
        border-color: var(--primary);
        background: var(--primary-l);
    }

    .mark-all-button {
        border: 0;
        border-radius: 8px;
        padding: 8px 13px;
        background: var(--primary);
        color: var(--white);
        font: inherit;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .mark-all-button:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .notification-row {
        display: grid;
        grid-template-columns: 11px minmax(0, 1fr) auto;
        gap: 14px;
        align-items: start;
        padding: 18px;
        border-bottom: 1px solid #EEF2F7;
        transition: background .15s;
    }

    .notification-row:last-child {
        border-bottom: 0;
    }

    .notification-row.unread {
        background: #F0F9FF;
    }

    .notification-row:hover {
        background: #F8FAFC;
    }

    .notification-state {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        margin-top: 6px;
        background: transparent;
    }

    .notification-row.unread .notification-state {
        background: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, .13);
    }

    .notification-main {
        min-width: 0;
    }

    .notification-title {
        display: inline-block;
        color: var(--text);
        font-size: 14px;
        font-weight: 750;
        line-height: 1.4;
        text-decoration: none;
    }

    .notification-title.has-link:hover {
        color: var(--primary-d);
    }

    .notification-message {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.55;
        margin-top: 4px;
        overflow-wrap: anywhere;
    }

    .notification-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 9px;
        color: #94A3B8;
        font-size: 11px;
    }

    .notification-type {
        padding: 3px 8px;
        border-radius: 999px;
        background: #E2E8F0;
        color: #475569;
        font-weight: 700;
    }

    .notification-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .notification-action {
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 6px 9px;
        background: var(--white);
        color: var(--muted);
        font: inherit;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    .notification-action:hover {
        color: var(--primary-d);
        border-color: var(--primary);
    }

    .notification-action.delete:hover {
        color: var(--danger);
        border-color: var(--danger);
    }

    .notifications-empty {
        padding: 65px 20px;
        text-align: center;
        color: var(--muted);
    }

    .notifications-empty strong {
        display: block;
        color: var(--text);
        font-size: 16px;
        margin-bottom: 6px;
    }

    .notifications-pagination {
        padding: 16px 18px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        background: #FAFBFD;
    }

    .notifications-pagination-summary {
        color: var(--muted);
        font-size: 12px;
        font-weight: 600;
    }

    .notifications-pagination-links {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .notifications-page-link {
        min-width: 34px;
        height: 34px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--white);
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        text-decoration: none;
        transition: border-color .15s, background .15s, color .15s;
    }

    .notifications-page-link:hover {
        border-color: var(--primary);
        background: var(--primary-l);
        color: var(--primary-d);
    }

    .notifications-page-link.current {
        border-color: var(--primary-d);
        background: var(--primary-d);
        color: var(--white);
    }

    .notifications-page-link.disabled,
    .notifications-page-link.ellipsis {
        border-color: var(--border);
        background: #F8FAFC;
        color: #94A3B8;
        cursor: default;
    }

    @media (max-width: 720px) {
        .notifications-header,
        .notifications-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .notification-row {
            grid-template-columns: 10px minmax(0, 1fr);
        }

        .notification-actions {
            grid-column: 2;
        }

        .notifications-pagination {
            align-items: flex-start;
            flex-direction: column;
        }

        .notifications-pagination-links {
            max-width: 100%;
            flex-wrap: wrap;
        }
    }
</style>
@endsection

@section('content')
<div class="notifications-page">
    <div class="notifications-header">
        <div class="notifications-heading">
            <h1>Notifications</h1>
            <p>{{ number_format($unreadCount) }} unread notification{{ $unreadCount === 1 ? '' : 's' }}</p>
        </div>
    </div>

    <section class="notifications-panel">
        <div class="notifications-toolbar">
            <div class="notification-filters">
                <a class="notification-filter {{ $status === '' ? 'active' : '' }}"
                   href="{{ route('admin.notifications.page') }}">All</a>
                <a class="notification-filter {{ $status === 'unread' ? 'active' : '' }}"
                   href="{{ route('admin.notifications.page', ['status' => 'unread']) }}">Unread</a>
                <a class="notification-filter {{ $status === 'read' ? 'active' : '' }}"
                   href="{{ route('admin.notifications.page', ['status' => 'read']) }}">Read</a>
            </div>

            <button type="button"
                    class="mark-all-button"
                    id="notificationsPageMarkAll"
                    {{ $unreadCount === 0 ? 'disabled' : '' }}>
                Mark all as read
            </button>
        </div>

        <div id="notificationsPageList">
            @forelse($notifications as $notification)
                @php
                    $link = $notification->getLink();
                    $isUnread = !$notification->is_read;
                @endphp
                <article class="notification-row {{ $isUnread ? 'unread' : '' }}"
                         id="notification-{{ $notification->_id }}">
                    <span class="notification-state" aria-hidden="true"></span>

                    <div class="notification-main">
                        <a class="notification-title {{ $link !== '#' ? 'has-link' : '' }}"
                           href="{{ $link }}"
                           @if($link !== '#')
                               data-notification-link="{{ $notification->_id }}"
                           @endif>
                            {{ $notification->title }}
                        </a>
                        <p class="notification-message">{{ $notification->message }}</p>
                        <div class="notification-meta">
                            <span class="notification-type">
                                {{ str($notification->type)->replace('_', ' ')->title() }}
                            </span>
                            <span title="{{ $notification->created_at?->format('M d, Y h:i A') }}">
                                {{ $notification->getTimeAgo() }}
                            </span>
                        </div>
                    </div>

                    <div class="notification-actions">
                        @if($isUnread)
                            <button type="button"
                                    class="notification-action"
                                    data-mark-read="{{ $notification->_id }}">
                                Mark read
                            </button>
                        @endif
                        <button type="button"
                                class="notification-action delete"
                                data-delete-notification="{{ $notification->_id }}">
                            Delete
                        </button>
                    </div>
                </article>
            @empty
                <div class="notifications-empty">
                    <strong>No notifications found</strong>
                    <span>New notifications will appear here.</span>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="notifications-pagination">
                <span class="notifications-pagination-summary">
                    Showing {{ number_format($notifications->firstItem()) }}–{{ number_format($notifications->lastItem()) }}
                    of {{ number_format($notifications->total()) }}
                </span>

                <nav class="notifications-pagination-links" aria-label="Notification pages">
                    @if($notifications->onFirstPage())
                        <span class="notifications-page-link disabled" aria-disabled="true" aria-label="Previous page">
                            &lsaquo;
                        </span>
                    @else
                        <a class="notifications-page-link"
                           href="{{ $notifications->previousPageUrl() }}"
                           rel="prev"
                           aria-label="Previous page">
                            &lsaquo;
                        </a>
                    @endif

                    @foreach($notifications->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                        @if($page === $notifications->currentPage())
                            <span class="notifications-page-link current"
                                  aria-current="page">{{ $page }}</span>
                        @elseif($page === 1 || $page === $notifications->lastPage() || abs($page - $notifications->currentPage()) <= 2)
                            <a class="notifications-page-link"
                               href="{{ $url }}"
                               aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @elseif($page === $notifications->currentPage() - 3 || $page === $notifications->currentPage() + 3)
                            <span class="notifications-page-link ellipsis" aria-hidden="true">&hellip;</span>
                        @endif
                    @endforeach

                    @if($notifications->hasMorePages())
                        <a class="notifications-page-link"
                           href="{{ $notifications->nextPageUrl() }}"
                           rel="next"
                           aria-label="Next page">
                            &rsaquo;
                        </a>
                    @else
                        <span class="notifications-page-link disabled" aria-disabled="true" aria-label="Next page">
                            &rsaquo;
                        </span>
                    @endif
                </nav>
            </div>
        @endif
    </section>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const markReadTemplate = @json(route('admin.notifications.mark-read', ['id' => '__ID__']));
    const deleteTemplate = @json(route('admin.notifications.destroy', ['id' => '__ID__']));
    const markAllUrl = @json(route('admin.notifications.read-all'));

    const request = async (url, method) => {
        const response = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to update notification.');
        }

        return data;
    };

    const markRead = async (id) => {
        await request(markReadTemplate.replace('__ID__', id), 'POST');
        const row = document.getElementById(`notification-${id}`);
        row?.classList.remove('unread');
        row?.querySelector('[data-mark-read]')?.remove();
    };

    document.querySelectorAll('[data-mark-read]').forEach((button) => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                await markRead(button.dataset.markRead);
            } catch (error) {
                button.disabled = false;
                alert(error.message);
            }
        });
    });

    document.querySelectorAll('[data-notification-link]').forEach((link) => {
        link.addEventListener('click', async (event) => {
            const destination = link.href;
            const row = link.closest('.notification-row');

            if (!row?.classList.contains('unread')) {
                return;
            }

            event.preventDefault();
            try {
                await markRead(link.dataset.notificationLink);
            } finally {
                window.location.href = destination;
            }
        });
    });

    document.querySelectorAll('[data-delete-notification]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (!confirm('Delete this notification?')) {
                return;
            }

            button.disabled = true;
            try {
                await request(deleteTemplate.replace('__ID__', button.dataset.deleteNotification), 'DELETE');
                button.closest('.notification-row')?.remove();
            } catch (error) {
                button.disabled = false;
                alert(error.message);
            }
        });
    });

    document.getElementById('notificationsPageMarkAll')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        button.disabled = true;

        try {
            await request(markAllUrl, 'POST');
            document.querySelectorAll('.notification-row.unread').forEach((row) => {
                row.classList.remove('unread');
                row.querySelector('[data-mark-read]')?.remove();
            });
        } catch (error) {
            button.disabled = false;
            alert(error.message);
        }
    });
});
</script>
@endsection

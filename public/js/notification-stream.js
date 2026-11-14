(function () {
    'use strict';

    if (window.NotificationStream) {
        return;
    }

    function meta(name) {
        var el = document.querySelector('meta[name="' + name + '"]');
        return el ? (el.getAttribute('content') || '') : '';
    }

    var listUrl = meta('notification-list-url') || 'notification-getall';
    var streamUrl = meta('notification-stream-url') || 'notification-stream';
    var readUrl = meta('notification-read-url') || 'notification-read';
    var csrf = meta('csrf-token');
    var sseEnabled = meta('notification-sse-enabled') !== '0';
    var pollSeconds = Math.max(10, parseInt(meta('notification-poll-seconds') || '30', 10) || 30);

    var lastId = 0;
    var unreadCount = 0;
    var source = null;
    var pollTimer = null;
    var reconnectTimer = null;
    var reconnectDelay = 5000;

    function listElements() {
        return [
            document.getElementById('bpNotifList'),
            document.getElementById('notifMenu')
        ].filter(Boolean);
    }

    function hasNotificationUi() {
        return listElements().length > 0;
    }

    function updateBadge(count) {
        unreadCount = Math.max(0, count);

        var pulse = document.querySelector('.main-header-notification .pulse-danger');
        if (pulse) {
            pulse.style.display = unreadCount > 0 ? '' : 'none';
        }

        var section = document.querySelector('.notif-icon-section');
        if (!section) {
            return;
        }

        var oldMark = section.querySelector('.notification-unread-mark');
        if (oldMark) {
            oldMark.remove();
        }

        if (unreadCount > 0) {
            var mark = document.createElement('h6');
            mark.className = 'text-danger notification-unread-mark';
            mark.textContent = '!';
            section.appendChild(mark);
        }
    }

    function createItem(notification) {
        var link = document.createElement('a');
        link.className = 'dropdown-item';
        link.href = '#';
        link.style.whiteSpace = 'normal';
        link.setAttribute('data-notification-id', notification.ID);

        var title = document.createElement('strong');
        title.textContent = notification.Title || 'Notification';
        link.appendChild(title);

        var created = document.createElement('small');
        created.className = 'text-muted d-block';
        created.textContent = notification.Created_At || '';
        link.appendChild(created);

        if (notification.Content) {
            var content = document.createElement('p');
            content.className = 'mb-0 mt-1';
            content.style.cssText = 'display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;';
            content.textContent = notification.Content;
            link.appendChild(content);
        }

        return link;
    }

    function renderList(items) {
        listElements().forEach(function (list) {
            list.innerHTML = '';

            if (!items.length) {
                var empty = document.createElement('div');
                empty.className = 'p-3 text-center text-muted notification-empty';
                empty.textContent = 'No notifications';
                list.appendChild(empty);
                return;
            }

            items.slice(0, 8).forEach(function (notification) {
                list.appendChild(createItem(notification));
            });
        });

        updateBadge(items.filter(function (item) {
            return item.Status === 'SENT';
        }).length);
    }

    function prepend(notification) {
        listElements().forEach(function (list) {
            var empty = list.querySelector('.notification-empty');
            if (empty) {
                empty.remove();
            }

            if (list.querySelector('[data-notification-id="' + notification.ID + '"]')) {
                return;
            }

            list.insertBefore(createItem(notification), list.firstChild);

            while (list.children.length > 8) {
                list.removeChild(list.lastChild);
            }
        });

        if (notification.Status === 'SENT') {
            updateBadge(unreadCount + 1);
        }
    }

    function fetchNotifications() {
        return fetch(listUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Notification request failed: ' + response.status);
                }
                return response.json();
            })
            .then(function (payload) {
                var items = (payload && payload.data) || [];
                renderList(items);
                if (items.length) {
                    lastId = Math.max(lastId, Math.max.apply(null, items.map(function (item) {
                        return parseInt(item.ID, 10) || 0;
                    })));
                }
                return items;
            })
            .catch(function (error) {
                if (window.console) {
                    console.warn('Notification refresh failed', error);
                }
                return [];
            });
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function startPolling() {
        if (pollTimer) {
            return;
        }
        pollTimer = setInterval(fetchNotifications, pollSeconds * 1000);
    }

    function closeStream() {
        if (source) {
            source.close();
            source = null;
        }
    }

    function scheduleReconnect() {
        if (reconnectTimer) {
            clearTimeout(reconnectTimer);
        }

        reconnectTimer = setTimeout(function () {
            reconnectTimer = null;
            if (!document.hidden) {
                startStream();
            }
        }, reconnectDelay);

        reconnectDelay = Math.min(30000, reconnectDelay * 2);
    }

    function startStream() {
        if (!sseEnabled || !window.EventSource) {
            startPolling();
            return;
        }

        closeStream();

        source = new EventSource(streamUrl + '?last_id=' + encodeURIComponent(lastId));

        source.addEventListener('open', function () {
            reconnectDelay = 5000;
            stopPolling();
        });

        source.addEventListener('notification', function (event) {
            try {
                var notification = JSON.parse(event.data);
                if (!notification || !notification.ID) {
                    return;
                }
                lastId = Math.max(lastId, parseInt(notification.ID, 10) || 0);
                prepend(notification);
            } catch (error) {
                if (window.console) {
                    console.warn('Invalid notification event', error);
                }
            }
        });

        source.onerror = function () {
            closeStream();
            startPolling();
            scheduleReconnect();
        };
    }

    function markAsRead(id) {
        fetch(readUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            credentials: 'same-origin',
            body: JSON.stringify({ id: id })
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Mark as read failed: ' + response.status);
                }
                return response.json();
            })
            .then(function () {
                return fetchNotifications();
            })
            .catch(function (error) {
                if (window.console) {
                    console.warn('Mark notification as read failed', error);
                }
            });
    }

    function handleVisibilityChange() {
        if (document.hidden) {
            closeStream();
            stopPolling();
            return;
        }

        fetchNotifications().then(function () {
            startStream();
        });
    }

    function init() {
        if (!hasNotificationUi()) {
            return;
        }

        fetchNotifications().then(function () {
            startStream();
        });
    }

    document.addEventListener('click', function (event) {
        var item = event.target.closest('[data-notification-id]');
        if (!item) {
            return;
        }

        event.preventDefault();
        markAsRead(item.getAttribute('data-notification-id'));
    });

    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('beforeunload', closeStream);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.NotificationStream = {
        refresh: fetchNotifications,
        reconnect: startStream
    };
})();

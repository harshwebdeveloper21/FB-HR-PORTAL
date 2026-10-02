// Service Worker for Push Notifications
const CACHE_NAME = 'sanvihr-pwa-v2'; // Changed version to force update

// Install event - cache resources (skip caching to avoid 404 errors)
self.addEventListener('install', (event) => {
  console.log('Service Worker installing...');
  // Skip waiting to activate immediately
  self.skipWaiting();
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Cache opened');
        // Don't cache anything that might not exist - just activate
        return Promise.resolve();
      })
      .catch((error) => {
        console.error('Cache error during install:', error);
        // Continue even if caching fails
        return Promise.resolve();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('Service Worker activating...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      // Take control of all pages immediately
      return self.clients.claim();
    })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Only handle http and https requests (skip chrome-extension://, etc.)
  if (!event.request.url.startsWith('http')) {
    return;
  }

  // Only handle GET requests with cache fallback
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        if (response) {
          return response;
        }
        return fetch(event.request).catch((error) => {
          console.warn('Fetch failed for:', event.request.url, error);
          // Return a simple offline fallback or propagate the error gracefully
          return new Response('Network error occurred. Please check your connection.', {
            status: 503,
            statusText: 'Service Unavailable',
            headers: new Headers({ 'Content-Type': 'text/plain' })
          });
        });
      })
  );
});

// Push event - handle push notifications
self.addEventListener('push', (event) => {
  console.log('Push event received:', event);
  
  let notificationData = {
    title: 'Fablead HRMS Notification',
    body: 'You have a new notification',
    icon: '/public/assets/images/fab_fav_icon.png',
    badge: '/public/assets/images/fab_fav_icon.png',
    vibrate: [200, 100, 200],
    data: {}
  };

  if (event.data) {
    try {
      const data = event.data.json();
      console.log('Push notification data:', data);
      notificationData = {
        title: data.title || notificationData.title,
        body: data.body || notificationData.body,
        icon: data.icon || notificationData.icon,
        badge: data.badge || notificationData.badge,
        vibrate: data.vibrate || notificationData.vibrate,
        data: data.data || {},
        requireInteraction: data.requireInteraction || false
      };
    } catch (e) {
      console.error('Error parsing push data:', e);
      notificationData.body = event.data.text() || notificationData.body;
    }
  }

  // Build notification options
  const notificationOptions = {
    body: notificationData.body,
    icon: notificationData.icon || '/public/assets/images/fab_fav_icon.png',
    badge: notificationData.badge || '/public/assets/images/fab_fav_icon.png',
    data: notificationData.data,
    requireInteraction: notificationData.requireInteraction || false,
    tag: 'fablead-notification',
    renotify: true,
    silent: false
  };

  // Add vibrate only if supported (mobile)
  if ('vibrate' in navigator) {
    notificationOptions.vibrate = notificationData.vibrate || [200, 100, 200];
  }

  // For desktop, add actions
  notificationOptions.actions = [
    {
      action: 'view',
      title: 'View'
    },
    {
      action: 'close',
      title: 'Close'
    }
  ];

  console.log('📢 Showing notification:', notificationData.title);
  console.log('📢 Options:', JSON.stringify(notificationOptions, null, 2));

  const promiseChain = self.registration.showNotification(notificationData.title, notificationOptions)
    .then(() => {
      console.log('✅ Notification displayed successfully');
    })
    .catch(error => {
      console.error('❌ Error showing notification:', error);
    });

  event.waitUntil(promiseChain);
});

// Notification click event - handle when user clicks notification
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const data = event.notification.data || {};
  
  console.log('🔔 Notification clicked - Full data:', JSON.stringify(data));

  // Function to determine the correct URL
  function determineUrl() {
    const notificationType = (data.type || '').toLowerCase();
    let origin = 'https://sanvihr.fableadtech.com'; // Default origin
    
    // Try to get origin from self.location if available
    if (self.location && self.location.origin) {
      origin = self.location.origin;
    }
    
    // Determine expected URL based on notification type (for validation)
    let expectedPath = '/dashboard'; // Default
    if (notificationType === 'checkin' || notificationType === 'checkout' || notificationType === 'attendance' || notificationType === 'checkout_reminder') {
      expectedPath = '/attendence';
    } else if (notificationType === 'leave_request' || notificationType === 'leave') {
      expectedPath = '/leaveview';
    }
    
    // Priority: Use data.url if provided (it's already an absolute URL from base_url())
    if (data.url) {
      let url = data.url.trim(); // Remove any whitespace
      
      // Ensure URL is absolute and properly formatted
      try {
        const urlObj = new URL(url);
        // Remove trailing slash from pathname (except for root)
        let pathname = urlObj.pathname;
        if (pathname.length > 1 && pathname.endsWith('/')) {
          pathname = pathname.slice(0, -1);
        }
        urlObj.pathname = pathname;
        const finalUrl = urlObj.href;
        
        // Validate that the URL path matches expected path for this notification type
        const urlPath = urlObj.pathname.toLowerCase();
        const expectedPathLower = expectedPath.toLowerCase();
        
        if (urlPath === expectedPathLower || urlPath.endsWith(expectedPathLower)) {
          console.log('🔔 URL from data.url - Valid:', finalUrl, 'Type:', notificationType);
          return finalUrl;
        } else {
          console.warn('⚠️ URL path mismatch. Expected:', expectedPath, 'Got:', urlPath, 'Using type-based routing instead');
          // Fall through to type-based routing
        }
      } catch (e) {
        console.error('❌ Invalid URL in data.url:', url, e);
        // If URL is relative, make it absolute
        if (url.startsWith('/')) {
          // Remove trailing slash
          if (url.length > 1 && url.endsWith('/')) {
            url = url.slice(0, -1);
          }
          const absoluteUrl = origin + url;
          console.log('🔔 Converted relative URL to absolute:', absoluteUrl);
          return absoluteUrl;
        }
        // If it's not a valid URL format, fall through to type-based routing
        console.warn('⚠️ Invalid URL format, falling back to type-based routing');
      }
    }
    
    // Fallback: determine URL based on notification type (this is more reliable)
    console.log('🔔 Using type-based routing for type:', notificationType);
    return origin + expectedPath;
  }

  const targetUrl = determineUrl();
  console.log('🔔 Final URL to open:', targetUrl, 'Type:', data.type);

  // Get dashboard URL as fallback
  function getDashboardUrl() {
    let origin = 'https://sanvihr.fableadtech.com';
    if (self.location && self.location.origin) {
      origin = self.location.origin;
  }
    return origin + '/dashboard';
  }

  const dashboardUrl = getDashboardUrl();

  // Check if URL exists before opening (to detect 404s)
  event.waitUntil(
    fetch(targetUrl, { method: 'HEAD', mode: 'no-cors' })
      .then(() => {
        // If fetch succeeds (or no-cors prevents checking), try to open the URL
        console.log('✅ URL check passed, opening:', targetUrl);
        return openOrFocusUrl(targetUrl);
      })
      .catch((error) => {
        console.warn('⚠️ URL check failed, will verify after opening:', error);
        // Still try to open, but we'll handle 404 detection in the page
        return openOrFocusUrl(targetUrl);
      })
  );

  function openOrFocusUrl(targetUrl) {
    return clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then((clientList) => {
        console.log('🔔 Found', clientList.length, 'open client(s)');
        
        // Normalize URLs for comparison (remove trailing slashes, query params, etc.)
        const normalizeUrl = (urlString) => {
          try {
            const urlObj = new URL(urlString);
            return urlObj.origin + urlObj.pathname.replace(/\/$/, '');
          } catch (e) {
            // If URL parsing fails, try basic normalization
            return urlString.replace(/\/$/, '').split('?')[0].split('#')[0];
          }
        };

        const targetUrlNormalized = normalizeUrl(targetUrl);
        console.log('🔔 Target URL normalized:', targetUrlNormalized);

        // Check if there's already a window/tab open with the target URL
        for (let i = 0; i < clientList.length; i++) {
          const client = clientList[i];
          const clientUrlNormalized = normalizeUrl(client.url);
          
          console.log('🔔 Comparing:', clientUrlNormalized, 'with', targetUrlNormalized);
          
          if (clientUrlNormalized === targetUrlNormalized && 'focus' in client) {
            console.log('✅ Focusing existing window:', client.url);
            // Try to navigate to the exact URL in case of slight differences
            if (client.navigate && typeof client.navigate === 'function') {
              return client.navigate(targetUrl).then(() => client.focus()).catch(() => client.focus());
            }
            return client.focus();
          }
        }
        
        // If not, open a new window/tab
        if (clients.openWindow) {
          console.log('✅ Opening new window/tab:', targetUrl);
          return clients.openWindow(targetUrl)
            .then((windowClient) => {
              // Send a message to the new window to check for 404 and redirect if needed
              if (windowClient) {
                // Wait a bit for the page to load, then check for 404
                setTimeout(() => {
                  windowClient.postMessage({
                    type: 'CHECK_404',
                    originalUrl: targetUrl,
                    dashboardUrl: dashboardUrl
                  }).catch(() => {
                    // Message sending failed, that's OK
                  });
                }, 1000);
              }
              return windowClient;
            })
            .catch((error) => {
              console.error('❌ Error opening window:', error);
              // If openWindow fails, try to open dashboard instead
              console.log('🔄 Falling back to dashboard:', dashboardUrl);
              return clients.openWindow(dashboardUrl).catch(() => {
                // If dashboard also fails, try to focus any existing client
                if (clientList.length > 0 && 'focus' in clientList[0]) {
                  return clientList[0].focus();
                }
                throw error;
              });
            });
        } else {
          console.error('❌ clients.openWindow is not available');
          // Try to focus any existing client as fallback
          if (clientList.length > 0 && 'focus' in clientList[0]) {
            return clientList[0].focus();
          }
        }
      })
      .catch((error) => {
        console.error('❌ Error in openOrFocusUrl:', error);
        // Last resort: try to open dashboard instead
        if (clients.openWindow && dashboardUrl) {
          console.log('🔄 Last resort: opening dashboard:', dashboardUrl);
          return clients.openWindow(dashboardUrl);
        }
      });
  }

  // Listen for messages from pages reporting 404 errors
  self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'PAGE_404') {
      console.log('🔔 Page reported 404, redirecting to dashboard');
      const dashboardUrl = getDashboardUrl();
      event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
          .then((clientList) => {
            for (let i = 0; i < clientList.length; i++) {
              const client = clientList[i];
              if (client.url === event.data.url || client.url.includes(event.data.url)) {
                if (client.navigate && typeof client.navigate === 'function') {
                  return client.navigate(dashboardUrl);
                } else if (client.postMessage) {
                  client.postMessage({ type: 'REDIRECT', url: dashboardUrl });
                }
              }
            }
            // If no matching client, open new window
            if (clients.openWindow) {
              return clients.openWindow(dashboardUrl);
        }
      })
  );
    }
  });
});

// Background sync (optional - for offline support)
self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

function doBackgroundSync() {
  // Implement background sync logic here
  return Promise.resolve();
}


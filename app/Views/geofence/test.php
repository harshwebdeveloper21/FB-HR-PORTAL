<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Geofence Live Tester</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Geofence Live Tester</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0 text-white"><i class="mdi mdi-crosshairs-gps me-2"></i> Live GPS Distance Monitor</h4>
                </div>
                <div class="card-body text-center p-5">
                    
                    <div id="tracker-status" class="mb-4">
                        <h2 class="display-4 text-secondary"><i class="mdi mdi-access-point-network mdi-spin"></i></h2>
                        <h4 class="text-muted">Waiting for GPS...</h4>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-muted mb-2">Distance from Branch</h5>
                                    <h2 id="distance-val" class="mb-0 text-primary">--</h2>
                                    <small class="text-muted">Meters</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-muted mb-2">GPS Accuracy</h5>
                                    <h2 id="accuracy-val" class="mb-0 text-info">--</h2>
                                    <small class="text-muted">Meters</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-muted mb-2">Backend Decision</h5>
                                    <h2 id="decision-val" class="mb-0 text-secondary">--</h2>
                                    <small class="text-muted">Status</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button id="start-test-btn" class="btn btn-primary px-4 py-2"><i class="mdi mdi-play me-2"></i>Start Testing (10s Ping)</button>
                        <button id="stop-test-btn" class="btn btn-danger px-4 py-2 ms-2" style="display:none;"><i class="mdi mdi-stop me-2"></i>Stop Testing</button>
                    </div>

                    <p class="text-muted mt-4 mb-0"><small><i class="mdi mdi-information-outline me-1"></i> If you walk outside the radius and keep testing for 30 seconds, HR will receive the push notification.</small></p>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    let testInterval = null;

    const startBtn = document.getElementById('start-test-btn');
    const stopBtn = document.getElementById('stop-test-btn');
    
    const distanceVal = document.getElementById('distance-val');
    const accuracyVal = document.getElementById('accuracy-val');
    const decisionVal = document.getElementById('decision-val');
    const trackerStatus = document.getElementById('tracker-status');

    const headers = {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    };

    function doPing() {
        if (!navigator.geolocation) {
            Swal.fire('Error', 'Geolocation is not supported by your browser', 'error');
            return;
        }

        trackerStatus.innerHTML = '<h2 class="display-4 text-primary"><i class="mdi mdi-access-point-network mdi-spin"></i></h2><h4 class="text-muted">Pinging GPS...</h4>';

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                fetch('/api/geofence/ping', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    })
                }).then(res => res.json()).then(data => {
                    trackerStatus.innerHTML = '<h2 class="display-4 text-success"><i class="mdi mdi-check-circle"></i></h2><h4 class="text-success">Tracking Active</h4>';
                    
                    if (data.distance !== undefined) {
                        distanceVal.innerText = Math.round(data.distance);
                        accuracyVal.innerText = Math.round(pos.coords.accuracy);
                        
                        if (data.status === 'alert') {
                            decisionVal.innerText = 'OUT OF BOUNDS';
                            decisionVal.className = 'mb-0 text-danger';
                            
                            Swal.fire({
                                icon: 'warning',
                                title: 'Employee Out of Bounds',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            decisionVal.innerText = 'INSIDE / SAFE';
                            decisionVal.className = 'mb-0 text-success';
                        }
                    } else {
                        decisionVal.innerText = data.message;
                    }

                }).catch(e => {
                    console.error("Geofence ping error:", e);
                    trackerStatus.innerHTML = '<h2 class="display-4 text-danger"><i class="mdi mdi-alert-circle"></i></h2><h4 class="text-danger">Network Error</h4>';
                });
            },
            (err) => {
                console.warn("Geofence GPS error:", err);
                trackerStatus.innerHTML = `<h2 class="display-4 text-danger"><i class="mdi mdi-crosshairs-off"></i></h2><h4 class="text-danger">GPS Error: ${err.message}</h4>`;
            },
            { enableHighAccuracy: true }
        );
    }

    startBtn.addEventListener('click', () => {
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-block';
        doPing(); // Initial ping
        testInterval = setInterval(doPing, 10000); // 10 seconds
    });

    stopBtn.addEventListener('click', () => {
        stopBtn.style.display = 'none';
        startBtn.style.display = 'inline-block';
        if (testInterval) clearInterval(testInterval);
        trackerStatus.innerHTML = '<h2 class="display-4 text-secondary"><i class="mdi mdi-pause-circle"></i></h2><h4 class="text-muted">Testing Paused</h4>';
    });
});
</script>
<?= $this->endSection() ?>

/**
 * JS chuyên biệt cho trang: Tìm Điểm Bán
 * Requirement: Lazy load Google Maps API (hiệu suất PageSpeed)
 */

document.addEventListener("DOMContentLoaded", () => {
    // Thẻ div chứa bản đồ (id="pharmacy-map-container")
    const mapContainer = document.getElementById("pharmacy-map-container");

    if (mapContainer && "IntersectionObserver" in window) {
        // 1. Khởi tạo lazy load: Chỉ load Map khi user cuộn gần tới vùng hiển thị
        let mapObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadGoogleMapsScript();
                    observer.unobserve(entry.target); // Xóa theo dõi sau khi load
                }
            });
        }, { rootMargin: "0px 0px 600px 0px" }); // Bắt đầu load trước khi user thấy 600px

        mapObserver.observe(mapContainer);
    } else if (mapContainer) {
        // Fallback cho trình duyệt cũ
        loadGoogleMapsScript();
    }
});

// 2. Chèn thẻ <script> API cực nhẹ không chặn render
function loadGoogleMapsScript() {
    if (window.google && window.google.maps) return; // Tránh load đè

    console.log("Lazy loading Google Maps API...");
    const script = document.createElement("script");
    
    // livespoMapData.apiKey do PHP chèn vào HTML
    script.src = `https://maps.googleapis.com/maps/api/js?key=${livespoMapData.apiKey}&libraries=places&callback=initPharmacyMap`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}

// 3. Callback sau khi trả API về: Vẽ Marker Cluster (Geolocation)
window.initPharmacyMap = function() {
    console.log("Google Maps API Initialized Successfully!");
    
    // Code logic lấy tọa độ từ livespoMapData.pharmacies và render Pin sẽ viết ở đây.
    // Kết hợp chung với GSAP (nếu cần pop-up hiệu ứng).
};

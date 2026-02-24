<?php
/**
 * Plugin Name: Plugin Jadwal Sholat
 * Description: Menampilkan jadwal sholat dengan pengaturan kustomisasi.
 * Version: 1.0
 * Author: bungrahman
 */

if (!defined('ABSPATH')) exit;

// Enqueue Bootstrap, Font Awesome & Media Uploader
function enqueue_bootstrap() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'jadwal-sholat') {
        return;
    }
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
    wp_enqueue_media();
    wp_enqueue_script('custom-upload', plugins_url('upload.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'enqueue_bootstrap');


// Register settings
function jadwal_sholat_register_settings() {
    $options = [
        'js_default_city', 'js_image', 'js_bg_form', 'js_bg_input', 'js_color_font',
        'js_bg_jadwal', 'js_color_nama', 'js_color_jam', 'js_font_size_jadwal',
        'js_font_size_jam', 'js_bg_tampilan'
    ];
    foreach ($options as $option) {
        register_setting('jadwal_sholat_options', $option);
    }
}
add_action('admin_init', 'jadwal_sholat_register_settings');

// Create admin menu
function jadwal_sholat_menu() {
    add_menu_page('Jadwal Sholat', 'Jadwal Sholat', 'manage_options', 'jadwal-sholat', 'jadwal_sholat_settings_page', 'dashicons-clock', 20);
}
add_action('admin_menu', 'jadwal_sholat_menu');

// Admin settings page
function jadwal_sholat_settings_page() {
    // Ambil daftar kota dari API (gunakan cache)
    $cache_key = 'jadwal_sholat_all_cities';
    $all_cities = get_transient($cache_key);

    if (!$all_cities) {
        $cities_url = "https://api.myquran.com/v2/sholat/kota/semua";
        $cities_response = wp_remote_get($cities_url);

        if (!is_wp_error($cities_response)) {
            $cities_data = json_decode(wp_remote_retrieve_body($cities_response), true);
            if (!empty($cities_data['data'])) {
                $all_cities = $cities_data['data'];
                set_transient($cache_key, $all_cities, 12 * HOUR_IN_SECONDS);
            }
        }
    }
    // Ambil kota default yang tersimpan
    $default_city = get_option('js_default_city', 'Batam');
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3" style="background-color: #ffffff; padding: 20px; border-radius: 5px;">
                <h4 class="mb-3">Pengaturan Jadwal Sholat</h4>
                <form method="post" action="options.php" enctype="multipart/form-data" class="needs-validation">
                    <?php settings_fields('jadwal_sholat_options'); ?>
                    <div class="mb-3">
                        <label class="form-label">Default Kota</label>
                        <select name="js_default_city" class="form-control">
                            <?php foreach ($all_cities as $kota): ?>
                                <option value="<?php echo esc_attr($kota['lokasi']); ?>" <?php selected($kota['lokasi'], $default_city); ?>>
                                    <?php echo esc_html($kota['lokasi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Gambar</label>
                        <div class="input-group">
                            <input type="text" id="js_image" name="js_image" class="form-control" value="<?php echo get_option('js_image'); ?>">
                            <button type="button" class="btn btn-secondary" id="upload_image_button">Upload</button>
                        </div>
                        <?php if (get_option('js_image')): ?>
                            <img src="<?php echo get_option('js_image'); ?>" class="img-thumbnail mt-2" width="150">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Latar Tampilan</label>
                        <input type="color" name="js_bg_tampilan" class="form-control form-control-color" value="<?php echo get_option('js_bg_tampilan', '#f8f9fa'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Latar Form</label>
                        <input type="color" name="js_bg_form" class="form-control form-control-color" value="<?php echo get_option('js_bg_form', '#f8f9fa'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Latar Input Pencarian</label>
                        <input type="color" name="js_bg_input" class="form-control form-control-color" value="<?php echo get_option('js_bg_input', '#ffffff'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Font Pencarian</label>
                        <input type="color" name="js_color_font" class="form-control form-control-color" value="<?php echo get_option('js_color_font', '#000000'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Latar Semua Jadwal & Do'a</label>
                        <input type="color" name="js_bg_jadwal" class="form-control form-control-color" value="<?php echo get_option('js_bg_jadwal', '#ffffff'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Nama Jadwal</label>
                        <input type="color" name="js_color_nama" class="form-control form-control-color" value="<?php echo get_option('js_color_nama', '#000000'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna Jam</label>
                        <input type="color" name="js_color_jam" class="form-control form-control-color" value="<?php echo get_option('js_color_jam', '#000000'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ukuran Font Jadwal</label>
                        <input type="number" name="js_font_size_jadwal" class="form-control" value="<?php echo get_option('js_font_size_jadwal', '16'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ukuran Font Jam</label>
                        <input type="number" name="js_font_size_jam" class="form-control" value="<?php echo get_option('js_font_size_jam', '20'); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </form>
            </div>
            <div class="col-md-9" style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                <h4 class="mb-3">Pratinjau Jadwal Sholat</h4>
                <div class="preview-box" style="background-color: <?php echo esc_attr(get_option('js_bg_tampilan', '#f8f9fa')); ?>; padding: 15px; border-radius: 5px;">
                    <div style="background-color: <?php echo esc_attr(get_option('js_bg_form', '#f8f9fa')); ?>; color: <?php echo esc_attr(get_option('js_color_font', '#000000')); ?>;">
                        <?php echo do_shortcode('[jadwal_sholat kota="' . esc_attr($default_city) . '"]'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#upload_image_button').click(function(e) {
            e.preventDefault();
            var image = wp.media({
                title: 'Upload Gambar',
                multiple: false
            }).open()
            .on('select', function(){
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                $('#js_image').val(image_url);
            });
        });
    });
    </script>
    <?php
}


// Shortcode Jadwal Sholat
function jadwal_sholat_shortcode($atts) {
    // wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    // wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
    $default_city_name = get_option('js_default_city', 'Batam'); // Kota default dari pengaturan
    $default_date = date('Y-m-d');

    // Ambil kota & tanggal dari URL, jika kosong gunakan default
    $city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : $default_city_name;
    $date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : $default_date;

    // Ambil daftar semua kota (cache 12 jam)
    $cache_key = 'jadwal_sholat_all_cities';
    $all_cities = get_transient($cache_key);

    if (!$all_cities) {
        $cities_url = "https://api.myquran.com/v2/sholat/kota/semua";
        $cities_response = wp_remote_get($cities_url);

        if (!is_wp_error($cities_response)) {
            $cities_data = json_decode(wp_remote_retrieve_body($cities_response), true);
            if (!empty($cities_data['data'])) {
                $all_cities = $cities_data['data'];
                set_transient($cache_key, $all_cities, 12 * HOUR_IN_SECONDS);
            }
        }
    }

    // Temukan ID kota berdasarkan nama kota yang dipilih
    $city_id = null;
    if (!empty($all_cities)) {
        foreach ($all_cities as $kota) {
            if (strcasecmp($kota['lokasi'], $city) === 0) {
                $city_id = $kota['id'];
                $city = $kota['lokasi']; // Pastikan dropdown tetap sesuai
                break;
            }
        }
    }

    // Ambil Jadwal Sholat dari API
    $jadwal = [];
    if ($city_id) {
        $schedule_url = "https://api.myquran.com/v2/sholat/jadwal/{$city_id}/{$date}";
        $schedule_response = wp_remote_get($schedule_url);

        if (!is_wp_error($schedule_response)) {
            $schedule_data = json_decode(wp_remote_retrieve_body($schedule_response), true);
            if (!empty($schedule_data['data']['jadwal'])) {
                $jadwal = $schedule_data['data']['jadwal'];
            }
        }
    }
    

    // Ambil Doa Harian
    $doa_url = "https://api.myquran.com/v2/doa/acak";
    $doa_response = wp_remote_get($doa_url);
    $doa_data = json_decode(wp_remote_retrieve_body($doa_response), true);
    $doa = $doa_data['data'] ?? null;

    ob_start();
    ?>
    <style>
        .js-prayer-wrapper {
            width: 100%;
            max-width: 100%;
            margin: 20px 0;
            padding: 15px;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .js-prayer-header {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
        }
        .js-header-left {
            flex: 1;
            min-width: 300px;
        }
        .js-header-right {
            flex: 1;
            min-width: 300px;
            text-align: center;
        }
        .js-header-right img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .js-digital-clock-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .js-date-text {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .js-clock-text {
            font-size: 48px;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
        }
        .js-search-form {
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .js-form-group {
            margin-bottom: 15px;
        }
        .js-form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .js-form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .js-jadwal-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 25px;
        }
        .js-jadwal-item {
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .js-jadwal-title {
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: capitalize;
        }
        .js-jadwal-time {
            font-weight: bold;
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        .js-countdown {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        .js-doa-section {
            margin-top: 30px;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .js-doa-title {
            font-weight: bold;
            margin-bottom: 15px;
        }
        .js-doa-text {
            font-size: 1.25rem;
            margin-bottom: 10px;
        }
        .js-doa-indo {
            font-size: 1.1rem;
            font-style: italic;
        }
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 5px !important;
        }
        .select2-container {
            width: 100% !important;
        }
        
        @media (max-width: 991px) {
            .js-jadwal-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 767px) {
            .js-header-left, .js-header-right {
                min-width: 100%;
            }
            .js-header-right img {
                height: 180px;
            }
            .js-clock-text {
                font-size: 36px;
            }
        }
    </style>
    <div class="js-prayer-wrapper" style="background: <?php echo get_option('js_bg_tampilan', '#f8f9fa'); ?>;">
        <div class="js-prayer-header">
            <div class="js-header-left">
                <div class="js-digital-clock-container">
                    <div class="js-date-text" style="color: <?php echo get_option('js_color_jam', '#000000'); ?>;">
                        <i class="fas fa-calendar-alt"></i> <span id="current-date">Loading...</span>
                    </div>
                    <div class="js-clock-text" style="color: <?php echo get_option('js_color_jam', '#000000'); ?>;">
                        <span id="digital-clock">Loading...</span>
                    </div>
                </div>
                
                <form method="GET" action="" class="js-search-form" style="background: <?php echo get_option('js_bg_form', '#f8f9fa'); ?>;">
                    <div class="js-form-group">
                        <label class="js-form-label" style="color: <?php echo get_option('js_color_font', '#000000'); ?>;">Nama Kota</label>
                        <select name="city" id="city-select" class="js-form-control">
                            <?php if (!empty($all_cities)): ?>
                                <?php foreach ($all_cities as $kota): ?>
                                    <option value="<?php echo esc_attr($kota['lokasi']); ?>" <?php selected($kota['lokasi'], $city); ?>>
                                        <?php echo esc_html($kota['lokasi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">Kota tidak tersedia</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="js-form-group">
                        <label class="js-form-label" style="color: <?php echo get_option('js_color_font', '#000000'); ?>;">Tanggal</label>
                        <input type="date" name="date" class="js-form-control" value="<?php echo esc_attr($date); ?>" onchange="this.form.submit()">
                    </div>
                    <noscript><button type="submit" class="js-btn-primary">Cari</button></noscript>
                    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
                    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
                </form>
            </div>
            <div class="js-header-right">
                <img src="<?php echo get_option('js_image', 'https://upload.wikimedia.org/wikipedia/commons/4/4b/Welcome_to_Batam.jpg'); ?>" alt="Gambar Kota">
            </div>
        </div>

        <?php if (!empty($jadwal)): ?>
        <div style="text-align: center; margin-top: 30px;">
            <h4 style="font-weight: bold; color: <?php echo get_option('js_color_jam', '#000000'); ?>;">Jadwal Sholat <?php echo esc_html($city); ?></h4>
        </div>
        <div class="js-jadwal-grid">
            <?php foreach (['imsak', 'subuh', 'terbit', 'dhuha', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $waktu) : ?>
                <div class="js-jadwal-item" style="background: <?php echo get_option('js_bg_jadwal', '#ffffff'); ?>;">
                    <h5 class="js-jadwal-title" style="color: <?php echo get_option('js_color_nama', '#000000'); ?>; font-size: <?php echo get_option('js_font_size_jadwal', '16'); ?>px;">
                        <?php echo ucfirst($waktu); ?>
                    </h5>
                    <p class="js-jadwal-time jadwal-waktu" data-waktu="<?php echo esc_attr($jadwal[$waktu] ?? '-'); ?>" 
                       style="color: <?php echo get_option('js_color_jam', '#000000'); ?>; 
                       font-size: <?php echo get_option('js_font_size_jam', '20'); ?>px;">
                       <?php echo esc_html($jadwal[$waktu] ?? '-'); ?>
                    </p>
                    <p class="countdown-timer js-countdown">Loading...</p>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($doa): ?>
        <div class="js-doa-section" style="background: <?php echo get_option('js_bg_jadwal', '#ffffff'); ?>;">
            <h5 class="js-doa-title" style="color: <?php echo get_option('js_color_nama', '#000000'); ?>;">Doa Harian: <?php echo esc_html($doa['judul'] ?? 'Doa'); ?></h5>
            <p class="js-doa-text" style="color: <?php echo get_option('js_color_nama', '#000000'); ?>;"><?php echo esc_html($doa['doa'] ?? $doa['ayat'] ?? ''); ?></p>
            <p class="js-doa-indo" style="color: <?php echo get_option('js_color_nama', '#000000'); ?>;"><?php echo esc_html($doa['artinya'] ?? $doa['indo'] ?? ''); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        function updateDate() {
            let today = new Date();
            let day = today.getDate().toString().padStart(2, "0");
            let month = (today.getMonth() + 1).toString().padStart(2, "0");
            let year = today.getFullYear();
            let formattedDate = `${day}/${month}/${year}`;
            let el = document.getElementById("current-date");
            if (el) el.textContent = `${formattedDate}`;
        }
        function updateClock() {
            let now = new Date();
            let hours = now.getHours().toString().padStart(2, "0");
            let minutes = now.getMinutes().toString().padStart(2, "0");
            let seconds = now.getSeconds().toString().padStart(2, "0");
            let el = document.getElementById("digital-clock");
            if (el) el.textContent = `${hours}:${minutes}:${seconds}`;
        }
        updateDate();
        setInterval(updateClock, 1000);

        function updateCountdown() {
            let now = new Date();
            let selectedDateStr = "<?php echo esc_js($date); ?>";
            let selectedDate = new Date(selectedDateStr);
            let timetable = document.querySelectorAll(".jadwal-waktu");
            
            if (selectedDate.toDateString() !== now.toDateString()) {
                document.querySelectorAll(".countdown-timer").forEach(el => el.style.display = "none");
                return;
            } else {
                document.querySelectorAll(".countdown-timer").forEach(el => el.style.display = "block");
            }

            timetable.forEach(el => {
                let timeStr = el.getAttribute("data-waktu");
                if (!timeStr || timeStr === "-") return;
                let [h, m] = timeStr.split(":").map(Number);
                let target = new Date();
                target.setHours(h, m, 0, 0);
                let diff = target - now;
                let countdownEl = el.nextElementSibling;
                if (diff > 0) {
                    let hl = Math.floor(diff / 3600000);
                    let ml = Math.floor((diff % 3600000) / 60000);
                    let sl = Math.floor((diff % 60000) / 1000);
                    countdownEl.textContent = `Sisa: ${hl}j ${ml}m ${sl}d`;
                } else {
                    countdownEl.textContent = "Selesai";
                }
            });
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });

    jQuery(document).ready(function($) {
        if ($.fn.select2) {
            $('#city-select').select2({
                placeholder: "Pilih Kota...",
                allowClear: true
            }).on('change', function() {
                $(this).closest("form").submit();
            });
        }
    });
    </script>



    <?php
    return ob_get_clean();
}
add_shortcode('jadwal_sholat', 'jadwal_sholat_shortcode');

// Shortcode Jadwal Sholat
function jadwal_sholat_only_shortcode($atts) {
    // wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    // wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
    $default_city_name = get_option('js_default_city', 'Batam'); // Kota default dari pengaturan
    $default_date = date('Y-m-d');

    // Ambil kota & tanggal dari URL, jika kosong gunakan default
    $city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : $default_city_name;
    $date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : $default_date;

    // Ambil daftar semua kota (cache 12 jam)
    $cache_key = 'jadwal_sholat_all_cities';
    $all_cities = get_transient($cache_key);

    if (!$all_cities) {
        $cities_url = "https://api.myquran.com/v2/sholat/kota/semua";
        $cities_response = wp_remote_get($cities_url);

        if (!is_wp_error($cities_response)) {
            $cities_data = json_decode(wp_remote_retrieve_body($cities_response), true);
            if (!empty($cities_data['data'])) {
                $all_cities = $cities_data['data'];
                set_transient($cache_key, $all_cities, 12 * HOUR_IN_SECONDS);
            }
        }
    }

    // Temukan ID kota berdasarkan nama kota yang dipilih
    $city_id = null;
    if (!empty($all_cities)) {
        foreach ($all_cities as $kota) {
            if (strcasecmp($kota['lokasi'], $city) === 0) {
                $city_id = $kota['id'];
                $city = $kota['lokasi']; // Pastikan dropdown tetap sesuai
                break;
            }
        }
    }

    // Ambil Jadwal Sholat dari API
    $jadwal = [];
    if ($city_id) {
        $schedule_url = "https://api.myquran.com/v2/sholat/jadwal/{$city_id}/{$date}";
        $schedule_response = wp_remote_get($schedule_url);

        if (!is_wp_error($schedule_response)) {
            $schedule_data = json_decode(wp_remote_retrieve_body($schedule_response), true);
            if (!empty($schedule_data['data']['jadwal'])) {
                $jadwal = $schedule_data['data']['jadwal'];
            }
        }
    }

    ob_start();
    if (!empty($jadwal)):
        ?>
        <style>
            .js-jadwal-grid-only {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
                margin-top: 20px;
                width: 100%;
            }
            .js-jadwal-item {
                padding: 15px;
                text-align: center;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            @media (max-width: 991px) {
                .js-jadwal-grid-only { grid-template-columns: repeat(2, 1fr); }
            }
        </style>
        <div style="text-align: center; margin-top: 20px;">
            <h4 style="font-weight: bold; color: <?php echo get_option('js_color_jam', '#000000'); ?>;">Jadwal Sholat <?php echo esc_html($city); ?></h4>
        </div>
        <div class="js-jadwal-grid-only">
            <?php foreach (['imsak', 'subuh', 'terbit', 'dhuha', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $waktu) : ?>
                <div class="js-jadwal-item" style="background: <?php echo get_option('js_bg_jadwal', '#ffffff'); ?>;">
                    <h5 style="font-weight: bold; margin: 0 0 10px 0; color: <?php echo get_option('js_color_nama', '#000000'); ?>; font-size: <?php echo get_option('js_font_size_jadwal', '16'); ?>px;">
                        <?php echo ucfirst($waktu); ?>
                    </h5>
                    <p style="font-weight: bold; margin: 0; color: <?php echo get_option('js_color_jam', '#000000'); ?>; 
                       font-size: <?php echo get_option('js_font_size_jam', '20'); ?>px; font-family: 'Courier New', Courier, monospace;">
                       <?php echo esc_html($jadwal[$waktu] ?? '-'); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
add_shortcode('jadwal_sholat_only', 'jadwal_sholat_only_shortcode');

// Shortcode Doa Harian
function doa_harian_shortcode($atts) {
    // wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    // wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
    // Ambil Doa Harian
    $doa_url = "https://api.myquran.com/v2/doa/acak";
    $doa_response = wp_remote_get($doa_url);
    $doa_data = json_decode(wp_remote_retrieve_body($doa_response), true);
    $doa = $doa_data['data'] ?? null;

    ob_start();
    if ($doa):
        ?>
        <style>
            .js-doa-only {
                margin-top: 20px;
                padding: 20px;
                text-align: center;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                width: 100%;
                box-sizing: border-box;
            }
        </style>
        <div class="js-doa-only" style="background: <?php echo get_option('js_bg_jadwal', '#ffffff'); ?>;">
            <h5 style="font-weight: bold; margin-bottom: 15px; color: <?php echo get_option('js_color_nama', '#000000'); ?>;">Doa Harian: <?php echo esc_html($doa['judul'] ?? 'Doa'); ?></h5>
            <p style="font-size: 1.25rem; margin-bottom: 10px; color: <?php echo get_option('js_color_nama', '#000000'); ?>;"><?php echo esc_html($doa['doa'] ?? $doa['ayat'] ?? ''); ?></p>
            <p style="font-size: 1.1rem; font-style: italic; color: <?php echo get_option('js_color_nama', '#000000'); ?>;"><?php echo esc_html($doa['artinya'] ?? $doa['indo'] ?? ''); ?></p>
        </div>
        <?php
    endif;
    return ob_get_clean();
}
add_shortcode('doa_harian', 'doa_harian_shortcode');

// Shortcode Jadwal Sholat Marquee
function jadwal_sholat_marquee_shortcode($atts) {
    $default_city_name = get_option('js_default_city', 'Batam');
    $default_date = date('Y-m-d');

    $city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : $default_city_name;
    $date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : $default_date;

    $cache_key = 'jadwal_sholat_all_cities';
    $all_cities = get_transient($cache_key);

    if (!$all_cities) {
        $cities_url = "https://api.myquran.com/v2/sholat/kota/semua";
        $cities_response = wp_remote_get($cities_url);
        if (!is_wp_error($cities_response)) {
            $cities_data = json_decode(wp_remote_retrieve_body($cities_response), true);
            if (!empty($cities_data['data'])) {
                $all_cities = $cities_data['data'];
                set_transient($cache_key, $all_cities, 12 * HOUR_IN_SECONDS);
            }
        }
    }

    $city_id = null;
    if (!empty($all_cities)) {
        foreach ($all_cities as $kota) {
            if (strcasecmp($kota['lokasi'], $city) === 0) {
                $city_id = $kota['id'];
                $city = $kota['lokasi'];
                break;
            }
        }
    }

    $city_name = esc_html($city);
    $date_formatted = date('d/m/Y', strtotime($date));
    $pills_html = "";

    if ($city_id) {
        $schedule_url = "https://api.myquran.com/v2/sholat/jadwal/{$city_id}/{$date}";
        $schedule_response = wp_remote_get($schedule_url);
        if (!is_wp_error($schedule_response)) {
            $schedule_data = json_decode(wp_remote_retrieve_body($schedule_response), true);
            if (!empty($schedule_data['data']['jadwal'])) {
                $jadwal = $schedule_data['data']['jadwal'];
                
                // Add City & Date pill first
                $pills_html .= "<span class='js-marquee-pill js-pill-info'>{$city_name} ({$date_formatted})</span>";
                
                foreach (['imsak', 'subuh', 'terbit', 'dhuha', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w) {
                    if (!empty($jadwal[$w])) {
                        $label = ucfirst($w);
                        $time = $jadwal[$w];
                        $pills_html .= "<span class='js-marquee-pill'>{$label}: <span class='js-pill-time'>{$time}</span></span>";
                    }
                }
            }
        }
    }

    if (empty($pills_html)) return "";

    ob_start();
    ?>
    <style>
        .js-marquee-container {
            width: 100%;
            overflow: hidden;
            padding: 15px 0;
            white-space: nowrap;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .js-marquee-content {
            display: inline-block;
            padding-left: 100%;
            animation: js-marquee 40s linear infinite;
        }
        .js-marquee-content:hover {
            animation-play-state: paused;
        }
        .js-marquee-pill {
            display: inline-block;
            background: <?php echo get_option('js_bg_jadwal', '#ffffff'); ?>;
            color: <?php echo get_option('js_color_nama', '#000000'); ?>;
            padding: 8px 18px;
            margin: 0 8px;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-weight: bold;
            font-size: <?php echo get_option('js_font_size_jadwal', '16'); ?>px;
            border: 1px solid rgba(0,0,0,0.03);
        }
        .js-pill-info {
            background: <?php echo get_option('js_color_nama', '#000000'); ?>;
            color: #ffffff;
        }
        .js-pill-time {
            color: <?php echo get_option('js_color_jam', '#000000'); ?>;
            font-family: 'Courier New', Courier, monospace;
        }
        @keyframes js-marquee {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }
    </style>
    <div class="js-marquee-container">
        <div class="js-marquee-content">
            <?php echo $pills_html; ?> &nbsp;&nbsp;&nbsp; <?php echo $pills_html; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('jadwal_sholat_marquee', 'jadwal_sholat_marquee_shortcode');









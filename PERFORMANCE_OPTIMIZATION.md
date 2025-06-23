# ⚡ Performance Optimization Guide

## 📊 Current Performance Analysis

### Database Queries
- **Current**: ~50-100 queries per admin page
- **Target**: <20 queries per page
- **Main Issues**: N+1 queries, missing indexes, inefficient joins

### Memory Usage
- **Current**: ~32-64MB per request
- **Target**: <32MB per request
- **Main Issues**: Large object instantiation, memory leaks

### Page Load Time
- **Current**: 3-5 seconds (admin pages)
- **Target**: <2 seconds
- **Main Issues**: Unoptimized queries, no caching

## 🎯 Optimization Strategies

### 1. Database Optimization

#### 1.1 Query Optimization
```php
// ❌ BAD: N+1 Query Problem
foreach ($users as $user) {
    $orders = MH_Query::init(null, 'affpro_user_order')
        ->where('user_id', $user['id'])
        ->get();
    $user['orders'] = $orders;
}

// ✅ GOOD: Single Query with JOIN
$users_with_orders = MH_Query::init(null, 'users u')
    ->select('u.*, GROUP_CONCAT(o.id) as order_ids')
    ->leftJoin('affpro_user_order o', 'u.ID', 'o.user_id')
    ->groupBy('u.ID')
    ->get();
```

#### 1.2 Add Database Indexes
```sql
-- Add indexes for frequently queried columns
ALTER TABLE wp_affpro_user_order ADD INDEX idx_user_id (user_id);
ALTER TABLE wp_affpro_user_order ADD INDEX idx_order_id (order_id);
ALTER TABLE wp_affpro_user_order ADD INDEX idx_status (status);
ALTER TABLE wp_affpro_user_order ADD INDEX idx_created_at (created_at);

-- Composite indexes for common query patterns
ALTER TABLE wp_affpro_user_order ADD INDEX idx_user_status (user_id, status);
ALTER TABLE wp_affpro_user_order ADD INDEX idx_order_status (order_id, status);
```

#### 1.3 Optimize MH_Query Class
```php
class MH_Query_Optimized extends MH_Query {
    private static $query_cache = [];
    
    public function get() {
        $cache_key = $this->getCacheKey();
        
        if (isset(self::$query_cache[$cache_key])) {
            return self::$query_cache[$cache_key];
        }
        
        $result = parent::get();
        self::$query_cache[$cache_key] = $result;
        
        return $result;
    }
    
    private function getCacheKey() {
        return md5(serialize([
            'select' => $this->select,
            'from' => $this->from,
            'where' => $this->where,
            'join' => $this->join
        ]));
    }
}
```

### 2. Caching Implementation

#### 2.1 Object Caching
```php
class AFF_Cache_Manager {
    private static $cache_group = 'aff_pro';
    private static $cache_expiry = 3600; // 1 hour
    
    public static function get($key) {
        return wp_cache_get($key, self::$cache_group);
    }
    
    public static function set($key, $data, $expiry = null) {
        $expiry = $expiry ?: self::$cache_expiry;
        return wp_cache_set($key, $data, self::$cache_group, $expiry);
    }
    
    public static function delete($key) {
        return wp_cache_delete($key, self::$cache_group);
    }
    
    public static function flush() {
        return wp_cache_flush_group(self::$cache_group);
    }
}
```

#### 2.2 Commission Calculation Caching
```php
class AFF_Commission_Calculator {
    public static function calculate($order_id, $user_id) {
        $cache_key = "commission_{$order_id}_{$user_id}";
        $cached = AFF_Cache_Manager::get($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Expensive calculation
        $commission = self::performCalculation($order_id, $user_id);
        
        // Cache for 24 hours
        AFF_Cache_Manager::set($cache_key, $commission, DAY_IN_SECONDS);
        
        return $commission;
    }
    
    public static function invalidate_cache($order_id, $user_id = null) {
        if ($user_id) {
            AFF_Cache_Manager::delete("commission_{$order_id}_{$user_id}");
        } else {
            // Invalidate all commissions for this order
            $pattern = "commission_{$order_id}_*";
            // Implementation depends on cache backend
        }
    }
}
```

#### 2.3 Transient Caching for Heavy Operations
```php
class AFF_Statistics {
    public static function get_dashboard_stats() {
        $cache_key = 'aff_dashboard_stats';
        $stats = get_transient($cache_key);
        
        if ($stats === false) {
            $stats = [
                'total_users' => self::count_total_users(),
                'total_orders' => self::count_total_orders(),
                'total_commission' => self::calculate_total_commission(),
                'monthly_stats' => self::get_monthly_stats()
            ];
            
            // Cache for 15 minutes
            set_transient($cache_key, $stats, 15 * MINUTE_IN_SECONDS);
        }
        
        return $stats;
    }
}
```

### 3. Asset Optimization

#### 3.1 CSS/JS Minification
```php
class AFF_Asset_Manager {
    public function enqueue_optimized_assets() {
        if (WP_DEBUG) {
            // Development - use unminified
            wp_enqueue_script('aff-admin', AFF_URL . 'admin/js/admin.js');
            wp_enqueue_style('aff-admin', AFF_URL . 'admin/css/admin.css');
        } else {
            // Production - use minified
            wp_enqueue_script('aff-admin', AFF_URL . 'admin/js/admin.min.js');
            wp_enqueue_style('aff-admin', AFF_URL . 'admin/css/admin.min.css');
        }
    }
    
    public function add_cache_busting() {
        add_filter('script_loader_src', [$this, 'add_version_to_assets']);
        add_filter('style_loader_src', [$this, 'add_version_to_assets']);
    }
    
    public function add_version_to_assets($src) {
        if (strpos($src, AFF_URL) !== false) {
            $src = add_query_arg('ver', AFF_Pro_VERSION, $src);
        }
        return $src;
    }
}
```

#### 3.2 Lazy Loading Implementation
```javascript
// Lazy load heavy components
const LazyDashboard = () => {
    return import('./components/Dashboard.vue');
};

const LazyUserManagement = () => {
    return import('./components/UserManagement.vue');
};

// Vue Router with lazy loading
const routes = [
    { path: '/', component: LazyDashboard },
    { path: '/users', component: LazyUserManagement }
];
```

### 4. Memory Optimization

#### 4.1 Object Pool Pattern
```php
class AFF_Object_Pool {
    private static $pools = [];
    
    public static function get($class_name) {
        if (!isset(self::$pools[$class_name])) {
            self::$pools[$class_name] = [];
        }
        
        if (empty(self::$pools[$class_name])) {
            return new $class_name();
        }
        
        return array_pop(self::$pools[$class_name]);
    }
    
    public static function release($object) {
        $class_name = get_class($object);
        if (!isset(self::$pools[$class_name])) {
            self::$pools[$class_name] = [];
        }
        
        // Reset object state
        if (method_exists($object, 'reset')) {
            $object->reset();
        }
        
        self::$pools[$class_name][] = $object;
    }
}
```

#### 4.2 Memory-Efficient Data Processing
```php
class AFF_Data_Processor {
    public function process_large_dataset($data) {
        // Process in chunks to avoid memory issues
        $chunk_size = 1000;
        $chunks = array_chunk($data, $chunk_size);
        
        foreach ($chunks as $chunk) {
            $this->process_chunk($chunk);
            
            // Free memory after each chunk
            unset($chunk);
            
            // Force garbage collection if needed
            if (memory_get_usage() > 50 * 1024 * 1024) { // 50MB
                gc_collect_cycles();
            }
        }
    }
}
```

### 5. AJAX Optimization

#### 5.1 Request Batching
```javascript
class AjaxBatcher {
    constructor() {
        this.queue = [];
        this.timeout = null;
    }
    
    add(request) {
        this.queue.push(request);
        
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        
        this.timeout = setTimeout(() => {
            this.flush();
        }, 100); // Batch requests within 100ms
    }
    
    flush() {
        if (this.queue.length === 0) return;
        
        const requests = [...this.queue];
        this.queue = [];
        
        // Send batched request
        axios.post(ajaxurl, {
            action: 'aff_batch_request',
            requests: requests
        });
    }
}
```

#### 5.2 Response Compression
```php
class AFF_Ajax_Handler {
    public function handle_batch_request() {
        $requests = $_POST['requests'] ?? [];
        $responses = [];
        
        foreach ($requests as $request) {
            $responses[] = $this->process_single_request($request);
        }
        
        // Compress response if large
        $response_data = json_encode($responses);
        if (strlen($response_data) > 1024) { // 1KB
            header('Content-Encoding: gzip');
            $response_data = gzencode($response_data);
        }
        
        echo $response_data;
        wp_die();
    }
}
```

## 📈 Performance Monitoring

### 1. Query Monitoring
```php
class AFF_Performance_Monitor {
    private static $query_log = [];
    
    public static function log_query($query, $execution_time) {
        self::$query_log[] = [
            'query' => $query,
            'time' => $execution_time,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ];
    }
    
    public static function get_slow_queries($threshold = 0.1) {
        return array_filter(self::$query_log, function($log) use ($threshold) {
            return $log['time'] > $threshold;
        });
    }
}
```

### 2. Memory Tracking
```php
class AFF_Memory_Tracker {
    private static $checkpoints = [];
    
    public static function checkpoint($name) {
        self::$checkpoints[$name] = [
            'memory' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'time' => microtime(true)
        ];
    }
    
    public static function get_report() {
        $report = [];
        $previous = null;
        
        foreach (self::$checkpoints as $name => $data) {
            $report[$name] = $data;
            if ($previous) {
                $report[$name]['diff'] = $data['memory'] - $previous['memory'];
            }
            $previous = $data;
        }
        
        return $report;
    }
}
```

## 🎯 Implementation Priority

### Phase 1: Critical (Week 1)
1. Fix security issue in MH_Query
2. Add database indexes
3. Implement basic caching

### Phase 2: High Impact (Week 2-3)
1. Optimize N+1 queries
2. Implement object caching
3. Add asset optimization

### Phase 3: Fine-tuning (Week 4)
1. Memory optimization
2. AJAX batching
3. Performance monitoring

## 📊 Success Metrics

- **Page Load Time**: <2 seconds
- **Database Queries**: <20 per page
- **Memory Usage**: <32MB per request
- **Cache Hit Ratio**: >80%
- **User Experience**: Smooth interactions

---

*Performance optimization roadmap - Created: 2025-06-23*
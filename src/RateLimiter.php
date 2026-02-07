<?php
/**
 * Rate limiting service
 */
class RateLimiter {
    private $maxAttempts;
    private $timeWindow;
    private $storagePath;
    
    public function __construct($maxAttempts = 5, $timeWindow = 300) {
        $this->maxAttempts = $maxAttempts;
        $this->timeWindow = $timeWindow;
        $this->storagePath = sys_get_temp_dir();
    }
    
    private function getCacheFile($identifier) {
        return $this->storagePath . '/rate_limit_' . md5($identifier);
    }
    
    public function isRateLimited($identifier) {
        $cacheFile = $this->getCacheFile($identifier);
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        $currentTime = time();
        
        // Clean old attempts
        $data = array_filter($data, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->timeWindow;
        });
        
        file_put_contents($cacheFile, json_encode(array_values($data)));
        
        return count($data) >= $this->maxAttempts;
    }
    
    public function recordAttempt($identifier) {
        $cacheFile = $this->getCacheFile($identifier);
        $currentTime = time();
        
        $attempts = [];
        if (file_exists($cacheFile)) {
            $attempts = json_decode(file_get_contents($cacheFile), true);
        }
        
        $attempts[] = $currentTime;
        
        // Keep only attempts within last hour
        $attempts = array_filter($attempts, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 3600;
        });
        
        file_put_contents($cacheFile, json_encode(array_values($attempts)));
    }
    
    public function getRemainingLockoutTime($identifier) {
        $cacheFile = $this->getCacheFile($identifier);
        
        if (!file_exists($cacheFile)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        $currentTime = time();
        
        // Clean old attempts
        $data = array_filter($data, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->timeWindow;
        });
        
        if (count($data) < $this->maxAttempts) {
            return 0;
        }
        
        $oldestAttempt = min($data);
        $lockoutEnd = $oldestAttempt + $this->timeWindow;
        
        return max(0, $lockoutEnd - $currentTime);
    }
    
    public function clearAttempts($identifier) {
        $cacheFile = $this->getCacheFile($identifier);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}
?>

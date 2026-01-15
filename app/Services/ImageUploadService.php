<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageUploadService
{
    /**
     * Upload and optimize player profile image
     */
    public function uploadPlayerImage(UploadedFile $file, $playerId = null): string
    {
        // Generate unique filename
        $filename = $playerId 
            ? 'player_' . $playerId . '_' . time() . '.webp'
            : 'player_' . Str::random(10) . '_' . time() . '.webp';
        
        // Create directory if it doesn't exist
        $directory = 'players/profiles';
        Storage::disk('public')->makeDirectory($directory);
        
        // Process and optimize image
        $image = Image::make($file);
        
        // Resize to optimal dimensions (max 400x400 for profile images)
        $image->resize(400, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // Convert to WebP for better compression
        $image->encode('webp', 80);
        
        // Store the processed image
        $path = $directory . '/' . $filename;
        Storage::disk('public')->put($path, $image->getEncoded());
        
        return $path;
    }

    /**
     * Upload and optimize team logo
     */
    public function uploadTeamLogo(UploadedFile $file, $teamId = null): string
    {
        // Generate unique filename
        $filename = $teamId 
            ? 'team_' . $teamId . '_logo_' . time() . '.webp'
            : 'team_logo_' . Str::random(10) . '_' . time() . '.webp';
        
        // Create directory if it doesn't exist
        $directory = 'teams/logos';
        Storage::disk('public')->makeDirectory($directory);
        
        // Process and optimize image
        $image = Image::make($file);
        
        // Resize to optimal dimensions (max 200x200 for logos)
        $image->resize(200, 200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // Convert to WebP for better compression
        $image->encode('webp', 85);
        
        // Store the processed image
        $path = $directory . '/' . $filename;
        Storage::disk('public')->put($path, $image->getEncoded());
        
        return $path;
    }

    /**
     * Upload and optimize venue image
     */
    public function uploadVenueImage(UploadedFile $file, $venueId = null): string
    {
        // Generate unique filename
        $filename = $venueId 
            ? 'venue_' . $venueId . '_' . time() . '.webp'
            : 'venue_' . Str::random(10) . '_' . time() . '.webp';
        
        // Create directory if it doesn't exist
        $directory = 'venues/images';
        Storage::disk('public')->makeDirectory($directory);
        
        // Process and optimize image
        $image = Image::make($file);
        
        // Resize to optimal dimensions (max 1200x800 for venue images)
        $image->resize(1200, 800, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // Convert to WebP for better compression
        $image->encode('webp', 75);
        
        // Store the processed image
        $path = $directory . '/' . $filename;
        Storage::disk('public')->put($path, $image->getEncoded());
        
        return $path;
    }

    /**
     * Delete old image and upload new one
     */
    public function replaceImage($oldPath, UploadedFile $newFile, $type, $id = null): string
    {
        // Delete old image if it exists
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        
        // Upload new image based on type
        switch ($type) {
            case 'player':
                return $this->uploadPlayerImage($newFile, $id);
            case 'team':
                return $this->uploadTeamLogo($newFile, $id);
            case 'venue':
                return $this->uploadVenueImage($newFile, $id);
            default:
                throw new \InvalidArgumentException("Unknown image type: {$type}");
        }
    }

    /**
     * Validate image file
     */
    public function validateImage(UploadedFile $file): array
    {
        $errors = [];
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            $errors[] = 'Invalid file type. Only JPEG, PNG, WebP, and GIF images are allowed.';
        }
        
        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size too large. Maximum size is 5MB.';
        }
        
        // Check image dimensions
        try {
            $imageInfo = getimagesize($file->getPathname());
            if (!$imageInfo) {
                $errors[] = 'Invalid image file.';
            } else {
                list($width, $height) = $imageInfo;
                if ($width < 100 || $height < 100) {
                    $errors[] = 'Image dimensions too small. Minimum size is 100x100 pixels.';
                }
                if ($width > 4000 || $height > 4000) {
                    $errors[] = 'Image dimensions too large. Maximum size is 4000x4000 pixels.';
                }
            }
        } catch (\Exception $e) {
            $errors[] = 'Unable to process image file.';
        }
        
        return $errors;
    }

    /**
     * Get image URL
     */
    public function getImageUrl($path): string
    {
        if (!$path) {
            return asset('images/default-avatar.png');
        }
        
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }
        
        return asset('images/default-avatar.png');
    }

    /**
     * Bulk optimize existing images
     */
    public function optimizeExistingImages(): array
    {
        $results = [
            'optimized' => 0,
            'errors' => 0,
            'total_size_before' => 0,
            'total_size_after' => 0,
        ];
        
        // Optimize player images
        $playerImages = Storage::disk('public')->allFiles('players/profiles');
        foreach ($playerImages as $image) {
            try {
                $result = $this->optimizeSingleImage($image);
                if ($result) {
                    $results['optimized']++;
                    $results['total_size_before'] += $result['before'];
                    $results['total_size_after'] += $result['after'];
                }
            } catch (\Exception $e) {
                $results['errors']++;
            }
        }
        
        // Optimize team logos
        $teamLogos = Storage::disk('public')->allFiles('teams/logos');
        foreach ($teamLogos as $logo) {
            try {
                $result = $this->optimizeSingleImage($logo);
                if ($result) {
                    $results['optimized']++;
                    $results['total_size_before'] += $result['before'];
                    $results['total_size_after'] += $result['after'];
                }
            } catch (\Exception $e) {
                $results['errors']++;
            }
        }
        
        return $results;
    }

    /**
     * Optimize a single image
     */
    private function optimizeSingleImage($path): ?array
    {
        $fullPath = Storage::disk('public')->path($path);
        
        if (!file_exists($fullPath)) {
            return null;
        }
        
        $sizeBefore = filesize($fullPath);
        
        // Only optimize if not already WebP
        if (!str_ends_with($path, '.webp')) {
            $image = Image::make($fullPath);
            
            // Determine optimal dimensions based on directory
            if (str_contains($path, 'players/profiles')) {
                $image->resize(400, 400, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $quality = 80;
            } elseif (str_contains($path, 'teams/logos')) {
                $image->resize(200, 200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $quality = 85;
            } else {
                $image->resize(1200, 800, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $quality = 75;
            }
            
            // Convert to WebP
            $image->encode('webp', $quality);
            
            // Save optimized version
            $newPath = str_replace(['.jpg', '.jpeg', '.png', '.gif'], '.webp', $path);
            Storage::disk('public')->put($newPath, $image->getEncoded());
            
            // Delete old file
            Storage::disk('public')->delete($path);
            
            $sizeAfter = Storage::disk('public')->size($newPath);
        } else {
            $sizeAfter = $sizeBefore;
        }
        
        return [
            'before' => $sizeBefore,
            'after' => $sizeAfter,
            'saved' => $sizeBefore - $sizeAfter,
        ];
    }
}

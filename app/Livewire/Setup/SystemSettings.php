<?php

namespace App\Livewire\Setup;

use App\Models\SystemSetting;
use Livewire\Component;
use Livewire\WithFileUploads;

class SystemSettings extends Component
{
    use WithFileUploads;

    public $settings = [];
    public $logo;
    public $favicon;
    public $activeTab = 'general';

    public function mount()
    {
        abort_if(auth()->user()->cannot('edit-system-settings'), 403);
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $allSettings = SystemSetting::all();
        foreach ($allSettings as $setting) {
            $this->settings[$setting->key] = $setting->value;
        }
    }

    public function save()
    {
        abort_if(auth()->user()->cannot('edit-system-settings'), 403);

        // Handle logo upload
        if ($this->logo) {
            $logoPath = $this->logo->store('logos', 'public');
            $this->settings['site_logo'] = $logoPath;
        }

        // Handle favicon upload
        if ($this->favicon) {
            $faviconPath = $this->favicon->store('favicons', 'public');
            $this->settings['site_favicon'] = $faviconPath;
        }

        // Save all settings
        foreach ($this->settings as $key => $value) {
            SystemSetting::set($key, $value);
        }

        session()->flash('message', __('Settings saved successfully.'));
        $this->loadSettings();
        $this->logo = null;
        $this->favicon = null;
    }

    public function render()
    {
        abort_if(auth()->user()->cannot('view-system-settings'), 403);
        
        return view('livewire.setup.system-settings')
            ->extends('layouts.skot')
            ->section('content');
    }
}

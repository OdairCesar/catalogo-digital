<x-layout.chat :title="$tool->title.' - OD Tec'" :description="$tool->description">
    <div class="mx-auto flex h-full w-full min-h-0 max-w-2xl flex-1 flex-col px-5 py-6 sm:px-8">
        <livewire:tool-chat :slug="$tool->slug" :key="$tool->slug" />
    </div>
</x-layout.chat>

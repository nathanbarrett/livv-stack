export type RealtimeMode = 'general' | 'kanban'

export interface RealtimeTranscript {
    id: string
    role: 'user' | 'assistant'
    text: string
    isPartial: boolean
    timestamp: Date
}

export interface RealtimeFunctionCall {
    call_id: string
    name: string
    arguments: string
}

export interface RealtimeClientSecret {
    value: string
    expires_at: number
}

export interface RealtimeToolParameter {
    type: string
    description?: string
    enum?: string[]
}

export interface RealtimeTool {
    type: 'function'
    name: string
    description: string
    parameters: {
        type: 'object'
        properties: Record<string, RealtimeToolParameter>
        required: string[]
    }
}

export interface TokenResponse {
    client_secret: RealtimeClientSecret
    tools: RealtimeTool[]
    instructions: string
}

export interface ExecuteFunctionRequest {
    function_name: string
    arguments: Record<string, unknown>
    call_id: string
}

export interface ExecuteFunctionResponse {
    call_id: string
    output: string
}

// OpenAI Realtime API Event Types
export interface RealtimeServerEvent {
    type: string
    event_id?: string
}

export interface RealtimeResponseDoneEvent extends RealtimeServerEvent {
    type: 'response.done'
    response?: {
        output?: RealtimeResponseOutputItem[]
    }
}

export interface RealtimeResponseOutputItem {
    type: 'message' | 'function_call'
    id?: string
    name?: string
    call_id?: string
    arguments?: string
}

export interface RealtimeTranscriptDeltaEvent extends RealtimeServerEvent {
    type: 'response.audio_transcript.delta'
    delta: string
}

export interface RealtimeTranscriptDoneEvent extends RealtimeServerEvent {
    type: 'response.audio_transcript.done'
    transcript: string
}

export interface RealtimeInputTranscriptionEvent extends RealtimeServerEvent {
    type: 'conversation.item.input_audio_transcription.completed'
    transcript: string
}

export interface RealtimeSpeechStartedEvent extends RealtimeServerEvent {
    type: 'input_audio_buffer.speech_started'
}

export interface RealtimeSpeechStoppedEvent extends RealtimeServerEvent {
    type: 'input_audio_buffer.speech_stopped'
}

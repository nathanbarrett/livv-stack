import { ref, readonly, type Ref, type DeepReadonly } from 'vue'
import axios from '@js/common/axios'
import { error as showError } from '@js/common/snackbar'
import type {
    RealtimeMode,
    RealtimeTranscript,
    TokenResponse,
    ExecuteFunctionRequest,
    ExecuteFunctionResponse,
    RealtimeResponseOutputItem,
} from '@js/types/realtime'

interface UseRealtimeChatReturn {
    isConnected: DeepReadonly<Ref<boolean>>
    isConnecting: DeepReadonly<Ref<boolean>>
    isRecording: DeepReadonly<Ref<boolean>>
    isAiSpeaking: DeepReadonly<Ref<boolean>>
    transcripts: DeepReadonly<Ref<RealtimeTranscript[]>>
    connect: (mode: RealtimeMode) => Promise<void>
    disconnect: () => Promise<void>
    sendTextMessage: (text: string) => void
}

// Module-level refs (singleton pattern since WebRTC connection is expensive)
const isConnected = ref(false)
const isConnecting = ref(false)
const isRecording = ref(false)
const isAiSpeaking = ref(false)
const transcripts = ref<RealtimeTranscript[]>([])

let pc: RTCPeerConnection | null = null
let dc: RTCDataChannel | null = null
let audioEl: HTMLAudioElement | null = null
let mediaStream: MediaStream | null = null

// Track partial transcripts by ID
const partialTranscriptId = ref<string | null>(null)
// Track pending user transcript (created when speech starts, updated when transcription completes)
const pendingUserTranscriptId = ref<string | null>(null)

export function useRealtimeChat(): UseRealtimeChatReturn {
    async function connect(mode: RealtimeMode): Promise<void> {
        if (isConnected.value || isConnecting.value) return

        try {
            isConnecting.value = true
            transcripts.value = []

            // 1. Get ephemeral token from backend
            const { data } = await axios.post<TokenResponse>('/api/chat/realtime/token', { mode })

            // 2. Create peer connection
            pc = new RTCPeerConnection()

            // 3. Setup audio output
            audioEl = document.createElement('audio')
            audioEl.autoplay = true
            pc.ontrack = (e) => {
                if (audioEl) {
                    audioEl.srcObject = e.streams[0]
                }
            }

            // 4. Add microphone track
            mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true })
            pc.addTrack(mediaStream.getTracks()[0])

            // 5. Create data channel
            dc = pc.createDataChannel('oai-events')
            dc.onopen = () => {
                // Configure session after channel opens
                if (dc) {
                    dc.send(JSON.stringify({
                        type: 'session.update',
                        session: {
                            instructions: data.instructions,
                            tools: data.tools,
                            tool_choice: 'auto',
                            input_audio_transcription: {
                                model: 'whisper-1',
                            },
                        },
                    }))
                }
                isConnected.value = true
                isConnecting.value = false
            }
            dc.onclose = () => {
                isConnected.value = false
            }
            dc.onerror = (error) => {
                console.error('Data channel error:', error)
                showError('Voice connection error')
            }
            dc.onmessage = handleServerEvent

            // 6. Exchange SDP
            const offer = await pc.createOffer()
            await pc.setLocalDescription(offer)

            const response = await fetch('https://api.openai.com/v1/realtime?model=gpt-4o-realtime-preview', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${data.client_secret.value}`,
                    'Content-Type': 'application/sdp',
                },
                body: offer.sdp,
            })

            if (!response.ok) {
                throw new Error(`Failed to connect: ${response.status}`)
            }

            const answerSdp = await response.text()
            await pc.setRemoteDescription({ type: 'answer', sdp: answerSdp })
        } catch (e) {
            console.error('Connect error:', e)
            showError('Failed to connect to voice assistant')
            isConnecting.value = false
            await disconnect()
        }
    }

    async function disconnect(): Promise<void> {
        mediaStream?.getTracks().forEach((t) => t.stop())
        dc?.close()
        pc?.close()
        audioEl?.remove()

        pc = null
        dc = null
        audioEl = null
        mediaStream = null

        isConnected.value = false
        isConnecting.value = false
        isRecording.value = false
        isAiSpeaking.value = false
        partialTranscriptId.value = null
        pendingUserTranscriptId.value = null
    }

    function handleServerEvent(event: MessageEvent): void {
        const data = JSON.parse(event.data)

        switch (data.type) {
            case 'response.audio_transcript.delta':
                handleTranscriptDelta(data.delta)
                break
            case 'response.audio_transcript.done':
                handleTranscriptDone(data.transcript)
                break
            case 'conversation.item.input_audio_transcription.completed':
                handleUserTranscriptionCompleted(data.transcript)
                break
            case 'response.done':
                isAiSpeaking.value = false
                handleFunctionCalls(data.response?.output)
                break
            case 'response.created':
                isAiSpeaking.value = true
                break
            case 'input_audio_buffer.speech_started':
                isRecording.value = true
                handleSpeechStarted()
                break
            case 'input_audio_buffer.speech_stopped':
                isRecording.value = false
                break
            case 'error':
                console.error('Realtime API error:', data.error)
                showError(data.error?.message || 'Voice assistant error')
                break
        }
    }

    function handleSpeechStarted(): void {
        // Create a placeholder user transcript when speech starts
        // This ensures the user message appears before the AI response
        pendingUserTranscriptId.value = crypto.randomUUID()
        transcripts.value.push({
            id: pendingUserTranscriptId.value,
            role: 'user',
            text: '...',
            isPartial: true,
            timestamp: new Date(),
        })
    }

    function handleUserTranscriptionCompleted(transcript: string): void {
        if (pendingUserTranscriptId.value) {
            // Update the placeholder with the actual transcription
            const idx = transcripts.value.findIndex((t) => t.id === pendingUserTranscriptId.value)
            if (idx !== -1) {
                transcripts.value[idx] = {
                    ...transcripts.value[idx],
                    text: transcript,
                    isPartial: false,
                }
            }
            pendingUserTranscriptId.value = null
        } else {
            // Fallback: add as new transcript if no pending one exists
            addTranscript('user', transcript, false)
        }
    }

    function handleTranscriptDelta(delta: string): void {
        if (!partialTranscriptId.value) {
            // Start new partial transcript
            partialTranscriptId.value = crypto.randomUUID()
            transcripts.value.push({
                id: partialTranscriptId.value,
                role: 'assistant',
                text: delta,
                isPartial: true,
                timestamp: new Date(),
            })
        } else {
            // Update existing partial transcript
            const idx = transcripts.value.findIndex((t) => t.id === partialTranscriptId.value)
            if (idx !== -1) {
                transcripts.value[idx] = {
                    ...transcripts.value[idx],
                    text: transcripts.value[idx].text + delta,
                }
            }
        }
    }

    function handleTranscriptDone(transcript: string): void {
        if (partialTranscriptId.value) {
            const idx = transcripts.value.findIndex((t) => t.id === partialTranscriptId.value)
            if (idx !== -1) {
                transcripts.value[idx] = {
                    ...transcripts.value[idx],
                    text: transcript,
                    isPartial: false,
                }
            }
            partialTranscriptId.value = null
        } else {
            addTranscript('assistant', transcript, false)
        }
    }

    function addTranscript(role: 'user' | 'assistant', text: string, isPartial: boolean): void {
        transcripts.value.push({
            id: crypto.randomUUID(),
            role,
            text,
            isPartial,
            timestamp: new Date(),
        })
    }

    async function handleFunctionCalls(output: RealtimeResponseOutputItem[] | undefined): Promise<void> {
        if (!output || !dc) return

        for (const item of output) {
            if (item.type === 'function_call' && item.call_id && item.name && item.arguments) {
                try {
                    const request: ExecuteFunctionRequest = {
                        function_name: item.name,
                        arguments: JSON.parse(item.arguments),
                        call_id: item.call_id,
                    }

                    const { data } = await axios.post<ExecuteFunctionResponse>(
                        '/api/chat/realtime/functions',
                        request
                    )

                    // Send function result back to OpenAI
                    dc.send(JSON.stringify({
                        type: 'conversation.item.create',
                        item: {
                            type: 'function_call_output',
                            call_id: item.call_id,
                            output: JSON.stringify(data.output),
                        },
                    }))

                    // Trigger response generation
                    dc.send(JSON.stringify({ type: 'response.create' }))
                } catch (e) {
                    console.error('Function execution error:', e)
                    // Send error back to OpenAI
                    dc.send(JSON.stringify({
                        type: 'conversation.item.create',
                        item: {
                            type: 'function_call_output',
                            call_id: item.call_id,
                            output: JSON.stringify({ error: 'Failed to execute function' }),
                        },
                    }))
                    dc.send(JSON.stringify({ type: 'response.create' }))
                }
            }
        }
    }

    function sendTextMessage(text: string): void {
        if (!dc || dc.readyState !== 'open') {
            showError('Voice assistant not connected')
            return
        }

        // Add user message to transcripts
        addTranscript('user', text, false)

        // Send text message through data channel
        dc.send(JSON.stringify({
            type: 'conversation.item.create',
            item: {
                type: 'message',
                role: 'user',
                content: [{ type: 'input_text', text }],
            },
        }))
        dc.send(JSON.stringify({ type: 'response.create' }))
    }

    return {
        isConnected: readonly(isConnected),
        isConnecting: readonly(isConnecting),
        isRecording: readonly(isRecording),
        isAiSpeaking: readonly(isAiSpeaking),
        transcripts: readonly(transcripts),
        connect,
        disconnect,
        sendTextMessage,
    }
}

import { describe, it, expect } from 'vitest'

describe('Example Test Suite', () => {
  it('should pass a simple test', () => {
    expect(1 + 1).toBe(2)
  })

  it('should check string equality', () => {
    const greeting = 'Hello, World!'
    expect(greeting).toBe('Hello, World!')
  })
})

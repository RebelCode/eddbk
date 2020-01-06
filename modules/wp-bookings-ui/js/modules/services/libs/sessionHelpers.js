const units = [
  60 * 60 * 24 * 7, // week
  60 * 60 * 24, // day
  60 * 60, // hour
  60, // minute
]

export function guessUnit (duration) {
  let result = false
  for (let i = 0; i < units.length; i++) {
    if (Math.floor(duration / units[i]) > 0) {
      result = i
      break
    }
  }
  return result !== false ? units.length - 1 - result : false
}
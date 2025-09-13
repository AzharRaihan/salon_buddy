/**
 * Save userData object to localStorage
 * @param {Object} userData
 */
export function setUserData(userData) {
    if (userData) {
      console.log("userData", userData);
      localStorage.setItem("userData", JSON.stringify(userData));
    }
  }
  
  /**
   * Get userData object from localStorage
   * @returns {Object|null}
   */
  export function getUserData() {
    const raw = localStorage.getItem("userData");
    if (raw) {
      try {
        return JSON.parse(raw);
      } catch (e) {
        console.error("Failed to parse userData:", e);
      }
    }
    return null;
  }
  
  /**
   * Remove userData from localStorage
   */
  export function removeUserData() {
    localStorage.removeItem("userData");
  }
  
  /**
   * Save accessToken to localStorage
   * @param {String} token
   */
  export function setAccessToken(token) {
    if (token) {
      localStorage.setItem('accessToken', token);
    }
  }
  
  /**
   * Get accessToken from localStorage
   * @returns {String|null}
   */
  export function getAccessToken() {
    return localStorage.getItem('accessToken');
  }
  
  /**
   * Remove accessToken from localStorage
   */
  export function removeAccessToken() {
    localStorage.removeItem('accessToken');
  }
  